<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Payment;
use App\Models\ShippingMethod;

use App\Services\GhnService;
use App\Services\MomoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    protected GhnService $ghnService;
    protected MomoService $momoService;

    public function __construct(GhnService $ghnService, MomoService $momoService)
    {
        $this->ghnService = $ghnService;
        $this->momoService = $momoService;
    }

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

        // Lấy danh sách tỉnh từ GHN
        $ghnProvinces = $this->ghnService->getProvinces();

        // Tính tổng trọng lượng giỏ hàng
        $totalWeight = $this->ghnService->calculateTotalWeight($cartItems);

        return view('orders.checkout', compact(
            'cartItems', 'addresses', 'shippingMethods',
            'subtotal', 'discount', 'coupon',
            'ghnProvinces', 'totalWeight'
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
            'payment_method'    => 'required|in:cod,bank_transfer,momo',
            'notes'             => 'nullable|string|max:500',
            'ghn_district_id'   => 'nullable|integer',
            'ghn_ward_code'     => 'nullable|string|max:20',
        ]);

        $cartItems = Cart::where('user_id', Auth::id())
            ->with(['product', 'variant'])
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Giỏ hàng của bạn đang trống.');
        }

        $couponCode = session('coupon_code');
        $coupon = $couponCode ? Coupon::where('code', $couponCode)->where('status', 'active')->first() : null;

        $subtotal = $cartItems->sum(fn($item) => $item->subtotal);
        $discount = $coupon ? $coupon->calculateDiscount($subtotal) : 0;

        // Tính phí ship qua GHN API
        $shippingFee = 0;
        $shippingMethodId = null;

        if ($request->ghn_district_id && $request->ghn_ward_code) {
            $totalWeight = $this->ghnService->calculateTotalWeight($cartItems);
            $feeResult = $this->ghnService->calculateShippingFee(
                (int) $request->ghn_district_id,
                $request->ghn_ward_code,
                $totalWeight,
                (int) $subtotal
            );

            if ($feeResult) {
                $shippingFee = (float) ($feeResult['total'] ?? 0);
            }
        }

        // Fallback: nếu GHN fail hoặc không có GHN data, dùng shipping_method_id
        if ($shippingFee == 0 && $request->shipping_method_id) {
            $shippingMethod = ShippingMethod::find($request->shipping_method_id);
            if ($shippingMethod) {
                $shippingFee = (float) $shippingMethod->price;
                $shippingMethodId = $shippingMethod->id;
            }
        }

        $total = $subtotal + $shippingFee - $discount;

        $orderCode = null;
        $orderId = null;
        $paymentMethod = $request->payment_method;

        DB::transaction(function () use (
            $request, $cartItems, $coupon, $subtotal, $shippingFee, $discount, $total,
            $shippingMethodId, $paymentMethod, &$orderCode, &$orderId
        ) {
            $order = Order::create([
                'user_id' => Auth::id(),
                'order_code' => Order::generateCode(),
                'status' => 'pending',
                'payment_method' => $paymentMethod,
                'payment_status' => 'pending',
                'shipping_method_id' => $shippingMethodId,
                'shipping_name' => $request->shipping_name,
                'shipping_phone' => $request->shipping_phone,
                'shipping_email' => $request->shipping_email,
                'shipping_province' => $request->shipping_province,
                'shipping_district' => $request->shipping_district,
                'shipping_ward' => $request->shipping_ward,
                'shipping_address' => "{$request->shipping_address_detail}, {$request->shipping_ward}, {$request->shipping_district}, {$request->shipping_province}",
                'ghn_district_id' => $request->ghn_district_id,
                'ghn_ward_code' => $request->ghn_ward_code,
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
                'method' => $paymentMethod,
                'amount' => $total,
                'status' => 'pending',
            ]);

            $orderCode = $order->order_code;
            $orderId = $order->id;

            session(['last_order_code' => $order->order_code]);
        });

        // Nếu thanh toán MoMo → redirect sang MoMo
        if ($paymentMethod === 'momo' && $orderCode) {
            $momoResult = $this->momoService->createPayment(
                $orderId,
                $orderCode,
                (int) $total,
                "Thanh toán đơn hàng {$orderCode} tại LuxBag"
            );

            if ($momoResult['success'] && $momoResult['payUrl']) {
                return redirect()->away($momoResult['payUrl']);
            }

            // MoMo failed → vẫn chuyển sang confirmation, hiện lỗi
            Log::warning('MoMo payment creation failed', ['order_code' => $orderCode, 'result' => $momoResult]);
            return redirect()->route('orders.confirmation', $orderCode)
                ->with('momo_error', $momoResult['message'] ?? 'Không thể tạo thanh toán MoMo. Vui lòng thử lại hoặc chọn phương thức khác.');
        }

        return redirect()->route('orders.confirmation', session('last_order_code'));
    }

    public function confirmation($orderCode)
    {
        $order = Order::where('order_code', $orderCode)
            ->where('user_id', Auth::id())
            ->with(['details', 'shippingMethod', 'payment'])
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
