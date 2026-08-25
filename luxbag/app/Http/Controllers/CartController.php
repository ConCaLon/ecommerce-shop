<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    private function getCartQuery()
    {
        $query = Cart::with(['product.primaryImage', 'variant']);
        if (Auth::check()) {
            return $query->where('user_id', Auth::id());
        }
        return $query->where('session_id', session()->getId());
    }

    public function index()
    {
        $cartItems = $this->getCartQuery()->get();
        $couponCode = session('coupon_code');
        $coupon = $couponCode ? Coupon::where('code', $couponCode)->first() : null;

        $subtotal = $cartItems->sum(fn($item) => $item->subtotal);
        $discount = $coupon ? $coupon->calculateDiscount($subtotal) : 0;

        return view('cart.index', compact('cartItems', 'subtotal', 'discount', 'coupon'));
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'integer|min:1|max:50',
        ]);

        $product = Product::findOrFail($request->product_id);

        if ($product->status !== 'active') {
            return response()->json(['success' => false, 'message' => 'Sản phẩm không còn bán.'], 422);
        }

        $quantity = $request->get('quantity', 1);
        $stock = $product->stock;

        if ($request->variant_id) {
            $variant = ProductVariant::find($request->variant_id);
            $stock = $variant?->stock ?? 0;
        }

        if ($stock < $quantity) {
            return response()->json(['success' => false, 'message' => "Chỉ còn {$stock} sản phẩm trong kho."], 422);
        }

        $conditions = [
            'product_id' => $request->product_id,
            'variant_id' => $request->variant_id,
        ];
        if (Auth::check()) {
            $conditions['user_id'] = Auth::id();
        } else {
            $conditions['session_id'] = session()->getId();
        }

        $cart = Cart::where($conditions)->first();

        if ($cart) {
            $newQty = $cart->quantity + $quantity;
            if ($newQty > $stock) {
                return response()->json(['success' => false, 'message' => "Không thể thêm. Số lượng tối đa trong giỏ: {$stock}."], 422);
            }
            $cart->update(['quantity' => $newQty]);
        } else {
            $data = array_merge($conditions, ['quantity' => $quantity]);
            if (!Auth::check()) {
                $data['session_id'] = session()->getId();
            }
            Cart::create($data);
        }

        $cartCount = $this->getCartQuery()->sum('quantity');

        return response()->json([
            'success' => true,
            'message' => 'Đã thêm vào giỏ hàng!',
            'cart_count' => $cartCount,
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate(['quantity' => 'required|integer|min:1|max:50']);

        $cart = $this->getCartQuery()->findOrFail($id);

        $stock = $cart->variant?->stock ?? $cart->product->stock;
        if ($request->quantity > $stock) {
            return response()->json(['success' => false, 'message' => "Chỉ còn {$stock} sản phẩm."], 422);
        }

        $cart->update(['quantity' => $request->quantity]);

        $cartItems = $this->getCartQuery()->get();
        $subtotal = $cartItems->sum(fn($item) => $item->subtotal);

        $couponCode = session('coupon_code');
        $coupon = $couponCode ? Coupon::where('code', $couponCode)->first() : null;
        $discount = $coupon ? $coupon->calculateDiscount($subtotal) : 0;

        return response()->json([
            'success' => true,
            'item_subtotal' => number_format($cart->subtotal, 0, ',', '.') . 'đ',
            'cart_subtotal' => number_format($subtotal, 0, ',', '.') . 'đ',
            'discount' => number_format($discount, 0, ',', '.') . 'đ',
            'total' => number_format($subtotal - $discount, 0, ',', '.') . 'đ',
            'cart_count' => $cartItems->sum('quantity'),
        ]);
    }

    public function remove($id)
    {
        $this->getCartQuery()->findOrFail($id)->delete();

        $cartItems = $this->getCartQuery()->get();
        $cartCount = $cartItems->sum('quantity');

        if (request()->ajax()) {
            return response()->json(['success' => true, 'cart_count' => $cartCount]);
        }
        return back()->with('success', 'Đã xóa sản phẩm khỏi giỏ hàng.');
    }

    public function clear()
    {
        $this->getCartQuery()->delete();
        session()->forget(['coupon_code']);
        return redirect()->route('cart.index')->with('success', 'Đã xóa toàn bộ giỏ hàng.');
    }

    public function applyCoupon(Request $request)
    {
        $request->validate(['coupon_code' => 'required|string']);

        $coupon = Coupon::where('code', strtoupper($request->coupon_code))->first();

        if (!$coupon || !$coupon->isValid()) {
            return back()->with('error', 'Mã giảm giá không hợp lệ hoặc đã hết hạn.');
        }

        if (Auth::check() && $coupon->hasUserExceededLimit(Auth::id())) {
            return back()->with('error', 'Bạn đã sử dụng mã giảm giá này rồi.');
        }

        $cartItems = $this->getCartQuery()->get();
        $subtotal = $cartItems->sum(fn($item) => $item->subtotal);

        if ($subtotal < $coupon->min_order_amount) {
            $min = number_format($coupon->min_order_amount, 0, ',', '.');
            return back()->with('error', "Đơn hàng tối thiểu {$min}đ để dùng mã này.");
        }

        session(['coupon_code' => $coupon->code]);
        return back()->with('success', 'Áp dụng mã giảm giá thành công!');
    }

    public function removeCoupon()
    {
        session()->forget('coupon_code');
        return back()->with('success', 'Đã xóa mã giảm giá.');
    }

    public function count()
    {
        $count = $this->getCartQuery()->sum('quantity');
        return response()->json(['count' => $count]);
    }
}
