<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Payment;
use App\Models\ShippingMethod;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function checkout()
    {
        $cartItems = Cart::where('user_id', Auth::id())
            ->with(['product.primaryImage', 'variant'])
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Giỏ hàng của bạn đang trống.');
        }

        $addresses = Auth::user()->addresses()->orderByDesc('is_default')->get();
        $shippingMethods = ShippingMethod::where('status', 'active')->get();

        $couponCode = session('coupon_code');
        $coupon = $couponCode ? Coupon::where('code', $couponCode)->first() : null;

        $subtotal = $cartItems->sum(fn($item) => $item->subtotal);
        $discount = $coupon ? $coupon->calculateDiscount($subtotal) : 0;

        return view('orders.checkout', compact(
            'cartItems', 'addresses', 'shippingMethods',
            'subtotal', 'discount', 'coupon'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'shipping_name'     => 'required|string|max:150',
            'shipping_phone'    => 'required|string|max:20',
            'shipping_email'    => 'required|email|max:255',
            'shipping_province' => 'required|string|max:100',
            'shipping_district' => 'required|string|max:100',
            'shipping_ward'     => 'required|string|max:100',
            'shipping_address_detail' => 'required|string',
            'shipping_method_id' => 'required|exists:shipping_methods,id',
            'payment_method'    => 'required|in:cod,bank_transfer',
            'notes'             => 'nullable|string|max:500',
        ]);

        $cartItems = Cart::where('user_id', Auth::id())
            ->with(['product', 'variant'])
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Giỏ hàng của bạn đang trống.');
        }

        $shippingMethod = ShippingMethod::findOrFail($request->shipping_method_id);

        $couponCode = session('coupon_code');
        $coupon = $couponCode ? Coupon::where('code', $couponCode)->where('status', 'active')->first() : null;

        $subtotal = $cartItems->sum(fn($item) => $item->subtotal);
        $shippingFee = $shippingMethod->price;
        $discount = $coupon ? $coupon->calculateDiscount($subtotal) : 0;
        $total = $subtotal + $shippingFee - $discount;

        DB::transaction(function () use ($request, $cartItems, $coupon, $subtotal, $shippingFee, $discount, $total, $shippingMethod) {
            $order = Order::create([
                'user_id' => Auth::id(),
                'order_code' => Order::generateCode(),
                'status' => 'pending',
                'payment_method' => $request->payment_method,
                'payment_status' => 'pending',
                'shipping_method_id' => $shippingMethod->id,
                'shipping_name' => $request->shipping_name,
                'shipping_phone' => $request->shipping_phone,
                'shipping_email' => $request->shipping_email,
                'shipping_province' => $request->shipping_province,
                'shipping_district' => $request->shipping_district,
                'shipping_ward' => $request->shipping_ward,
                'shipping_address' => "{$request->shipping_address_detail}, {$request->shipping_ward}, {$request->shipping_district}, {$request->shipping_province}",
                'coupon_id' => $coupon?->id,
                'coupon_code' => $coupon?->code,
                'subtotal' => $subtotal,
                'shipping_fee' => $shippingFee,
                'discount_amount' => $discount,
                'total' => $total,
                'notes' => $request->notes,
            ]);

            // Create order details
            foreach ($cartItems as $item) {
                $primaryImage = $item->product->primaryImage;
                OrderDetail::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'variant_id' => $item->variant_id,
                    'product_name' => $item->product->name,
                    'product_sku' => $item->product->sku,
                    'variant_info' => $item->variant?->label,
                    'product_image' => $primaryImage?->image_path,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'subtotal' => $item->subtotal,
                ]);

                // Update product stock and sold_count
                $item->product->decrement('stock', $item->quantity);
                $item->product->increment('sold_count', $item->quantity);
                if ($item->variant) {
                    $item->variant->decrement('stock', $item->quantity);
                }
            }

            // Mark coupon as used
            if ($coupon) {
                $coupon->increment('used_count');
                CouponUsage::create([
                    'coupon_id' => $coupon->id,
                    'user_id' => Auth::id(),
                    'order_id' => $order->id,
                ]);
            }

            // Clear cart
            Cart::where('user_id', Auth::id())->delete();
            session()->forget('coupon_code');

            // Create payment record
            Payment::create([
                'order_id' => $order->id,
                'method' => $request->payment_method,
                'amount' => $total,
                'status' => 'pending',
            ]);

            // Fire order notification (email)
            // Mail::to($request->shipping_email)->queue(new OrderConfirmationMail($order));

            session(['last_order_code' => $order->order_code]);
        });

        return redirect()->route('orders.confirmation', session('last_order_code'));
    }

    public function confirmation($orderCode)
    {
        $order = Order::where('order_code', $orderCode)
            ->where('user_id', Auth::id())
            ->with(['details', 'shippingMethod'])
            ->firstOrFail();

        return view('orders.confirmation', compact('order'));
    }

    public function history()
    {
        $orders = Order::where('user_id', Auth::id())
            ->with(['details.product.primaryImage'])
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('orders.history', compact('orders'));
    }

    public function show($orderCode)
    {
        $order = Order::where('order_code', $orderCode)
            ->where('user_id', Auth::id())
            ->with(['details.product.primaryImage', 'details.variant', 'shippingMethod', 'payment'])
            ->firstOrFail();

        return view('orders.show', compact('order'));
    }

    public function cancel(Request $request, $orderCode)
    {
        $order = Order::where('order_code', $orderCode)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if (!$order->canBeCancelled()) {
            return back()->with('error', 'Không thể hủy đơn hàng này.');
        }

        $request->validate(['reason' => 'nullable|string|max:500']);

        // Restore stock
        DB::transaction(function () use ($order, $request) {
            foreach ($order->details as $detail) {
                if ($detail->product) {
                    $detail->product->increment('stock', $detail->quantity);
                    $detail->product->decrement('sold_count', $detail->quantity);
                }
                if ($detail->variant) {
                    $detail->variant->increment('stock', $detail->quantity);
                }
            }

            $order->update([
                'status' => 'cancelled',
                'cancelled_reason' => $request->reason ?? 'Khách hàng hủy đơn',
                'cancelled_at' => now(),
            ]);
        });

        return back()->with('success', 'Đơn hàng đã được hủy thành công.');
    }
}
