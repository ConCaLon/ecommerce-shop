<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CouponController extends Controller
{
    public function index()
    {
        $coupons = Coupon::withCount('usages')->orderByDesc('created_at')->paginate(20);
        return view('admin.coupons.index', compact('coupons'));
    }

    public function create()  { return view('admin.coupons.create'); }

    public function store(Request $request)
    {
        $request->validate([
            'code'                => 'required|string|max:50|unique:coupons',
            'type'                => 'required|in:percent,fixed',
            'value'               => 'required|numeric|min:0',
            'min_order_amount'    => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'max_uses'            => 'nullable|integer|min:1',
            'per_user_limit'      => 'nullable|integer|min:1',
            'starts_at'           => 'required|date',
            'expires_at'          => 'nullable|date|after:starts_at',
            'description'         => 'nullable|string|max:255',
        ]);

        Coupon::create(array_merge($request->all(), ['code' => strtoupper($request->code)]));
        return redirect()->route('admin.coupons.index')->with('success', 'Mã giảm giá đã được tạo.');
    }

    public function edit(Coupon $coupon) { return view('admin.coupons.edit', compact('coupon')); }

    public function update(Request $request, Coupon $coupon)
    {
        $request->validate([
            'type'       => 'required|in:percent,fixed',
            'value'      => 'required|numeric|min:0',
            'status'     => 'required|in:active,inactive,expired',
            'expires_at' => 'nullable|date',
        ]);
        $coupon->update($request->all());
        return redirect()->route('admin.coupons.index')->with('success', 'Mã giảm giá đã được cập nhật.');
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();
        return back()->with('success', 'Mã giảm giá đã được xóa.');
    }

    public function show(Coupon $coupon) { return view('admin.coupons.show', compact('coupon')); }
}
