@extends('layouts.app')

@section('title', 'Giỏ Hàng')
@section('meta_description', 'Xem và quản lý giỏ hàng của bạn tại Bloom & Bold.')

@section('content')
<div class="container py-5">
    <h1 class="h3 fw-700 mb-4">🛍 Giỏ Hàng Của Bạn</h1>

    @if($cartItems->isEmpty())
    <div style="text-align:center;padding:80px 20px;">
        <div style="font-size:80px;margin-bottom:24px;opacity:.4;">🛒</div>
        <h4 style="color:#555;margin-bottom:12px;">Giỏ hàng của bạn đang trống</h4>
        <p style="color:#888;margin-bottom:24px;">Hãy khám phá các sản phẩm túi xách cao cấp của chúng tôi!</p>
        <a href="{{ route('products.index') }}" class="btn btn-dark btn-lg px-4">Mua Sắm Ngay</a>
    </div>
    @else

    <div class="row g-4">
        {{-- ─── Cart Items ─── --}}
        <div class="col-lg-8">
            <div style="background:#fff;border-radius:16px;box-shadow:0 2px 20px rgba(0,0,0,.06);overflow:hidden;">
                <div style="padding:20px 24px;border-bottom:1px solid #f0f0f0;display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-weight:700;font-size:15px;">{{ $cartItems->sum('quantity') }} sản phẩm</span>
                    <form method="DELETE" action="{{ route('cart.clear') }}">
                        @csrf @method('DELETE')
                        <button type="submit" style="background:none;border:none;color:#e74c3c;font-size:13px;cursor:pointer;font-weight:600;"
                            onclick="return confirm('Xóa toàn bộ giỏ hàng?')">
                            <i class="fas fa-trash me-1"></i>Xóa tất cả
                        </button>
                    </form>
                </div>

                @foreach($cartItems as $item)
                <div class="cart-item" id="cart-item-{{ $item->id }}" style="padding:20px 24px;border-bottom:1px solid #f8f8f8;display:flex;gap:16px;align-items:center;">
                    {{-- Product image --}}
                    <a href="{{ route('products.show', $item->product->slug) }}">
                        @if($item->product->primaryImage)
                            <img src="{{ $item->product->primaryImage->url }}" alt="{{ $item->product->name }}"
                                 style="width:80px;height:80px;border-radius:10px;object-fit:cover;border:1px solid #eee;flex-shrink:0;">
                        @else
                            <div style="width:80px;height:80px;border-radius:10px;background:#f5f5f5;display:flex;align-items:center;justify-content:center;">
                                <i class="fas fa-box" style="color:#ccc;font-size:24px;"></i>
                            </div>
                        @endif
                    </a>

                    {{-- Info --}}
                    <div class="flex-1">
                        <a href="{{ route('products.show', $item->product->slug) }}"
                           style="font-weight:700;font-size:14.5px;color:#1a1d24;text-decoration:none;display:block;margin-bottom:4px;">
                            {{ $item->product->name }}
                        </a>
                        @if($item->variant)
                        <div style="font-size:12px;color:#888;margin-bottom:8px;">{{ $item->variant->label }}</div>
                        @endif
                        <div style="font-size:14px;font-weight:700;color:#6345B5;">{{ vnd($item->unit_price) }}</div>
                        @if($item->product->is_on_sale)
                        <div style="font-size:12px;color:#aaa;text-decoration:line-through;">{{ vnd($item->product->price) }}</div>
                        @endif
                    </div>

                    {{-- Quantity --}}
                    <div class="d-flex align-items-center" style="gap:0;">
                        <button type="button" class="qty-btn" onclick="updateQty({{ $item->id }}, -1)"
                            style="width:34px;height:34px;border:1px solid #ddd;border-radius:8px 0 0 8px;background:#f8f8f8;cursor:pointer;font-size:16px;display:flex;align-items:center;justify-content:center;">−</button>
                        <input type="number" id="qty-{{ $item->id }}" value="{{ $item->quantity }}" min="1" max="50"
                            style="width:52px;height:34px;border:1px solid #ddd;border-top:1px solid #ddd;border-bottom:1px solid #ddd;border-left:none;border-right:none;text-align:center;font-weight:700;font-size:14px;outline:none;"
                            onchange="updateQtyDirect({{ $item->id }}, this.value)">
                        <button type="button" class="qty-btn" onclick="updateQty({{ $item->id }}, 1)"
                            style="width:34px;height:34px;border:1px solid #ddd;border-radius:0 8px 8px 0;background:#f8f8f8;cursor:pointer;font-size:16px;display:flex;align-items:center;justify-content:center;">+</button>
                    </div>

                    {{-- Subtotal --}}
                    <div style="min-width:100px;text-align:right;">
                        <div id="subtotal-{{ $item->id }}" style="font-weight:700;font-size:15px;color:#2D243D;">{{ vnd($item->subtotal) }}</div>
                    </div>

                    {{-- Remove --}}
                    <button type="button" onclick="removeItem({{ $item->id }})"
                        style="background:none;border:none;color:#e74c3c;font-size:16px;cursor:pointer;padding:4px 8px;flex-shrink:0;"
                        title="Xóa">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                @endforeach
            </div>
        </div>

        {{-- ─── Summary ─── --}}
        <div class="col-lg-4">
            {{-- Coupon --}}
            <div style="background:#fff;border-radius:16px;box-shadow:0 2px 20px rgba(123,94,201,.06);border:1px solid #EAE1F8;padding:24px;margin-bottom:20px;">
                <h6 style="font-weight:700;font-size:15px;margin-bottom:16px;">🎟 Mã Giảm Giá</h6>
                @if($coupon)
                <div style="background:rgba(39,174,96,.08);border:1px solid rgba(39,174,96,.2);border-radius:10px;padding:12px 16px;display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                    <div>
                        <span style="font-family:monospace;font-weight:700;color:#27AE60;font-size:15px;">{{ $coupon->code }}</span>
                        <div style="font-size:12px;color:#27AE60;margin-top:2px;">Tiết kiệm {{ vnd($discount) }}</div>
                    </div>
                    <form action="{{ route('cart.coupon.remove') }}" method="DELETE">
                        @csrf @method('DELETE')
                        <button type="submit" style="background:none;border:none;color:#e74c3c;cursor:pointer;font-size:18px;">×</button>
                    </form>
                </div>
                @else
                <form action="{{ route('cart.coupon.apply') }}" method="POST" class="d-flex gap-2">
                    @csrf
                    <input type="text" name="coupon_code" class="form-control"
                           placeholder="Nhập mã giảm giá..." style="border-radius:8px;font-size:13.5px;border:1.5px solid #E2D5F7;"
                           value="{{ old('coupon_code') }}">
                    <button type="submit" style="background:linear-gradient(135deg,#8A6FD6,#6345B5);color:#fff;border:none;padding:10px 16px;border-radius:8px;font-weight:600;font-size:13px;white-space:nowrap;cursor:pointer;">Áp dụng</button>
                </form>
                @endif
            </div>

            {{-- Order Summary --}}
            <div style="background:#fff;border-radius:16px;box-shadow:0 2px 20px rgba(123,94,201,.06);border:1px solid #EAE1F8;padding:24px;">
                <h6 style="font-weight:700;font-size:15px;margin-bottom:20px;">Tóm Tắt Đơn Hàng</h6>

                <div class="d-flex justify-content-between mb-3">
                    <span style="color:#666;">Tạm tính</span>
                    <span id="cart-subtotal" style="font-weight:600;">{{ vnd($subtotal) }}</span>
                </div>
                @if($discount > 0)
                <div class="d-flex justify-content-between mb-3">
                    <span style="color:#666;">Giảm giá</span>
                    <span id="cart-discount" style="font-weight:600;color:#27AE60;">-{{ vnd($discount) }}</span>
                </div>
                @endif
                <div style="border-top:1px solid #F3EDFB;padding-top:16px;margin-top:4px;" class="d-flex justify-content-between mb-4">
                    <span style="font-weight:700;font-size:16px;">Tổng cộng</span>
                    <span id="cart-total" style="font-weight:800;font-size:20px;color:#6345B5;">{{ vnd($subtotal - $discount) }}</span>
                </div>
                <div style="font-size:12px;color:#888;margin-bottom:20px;text-align:center;">
                    <i class="fas fa-shipping-fast me-1 text-success"></i>
                    {{ $subtotal >= setting('free_shipping_threshold', 500000) ? 'Bạn được miễn phí vận chuyển!' : 'Mua thêm '.vnd(setting('free_shipping_threshold', 500000) - $subtotal).' để miễn phí vận chuyển' }}
                </div>

                @auth
                <a href="{{ route('orders.checkout') }}"
                   style="display:block;background:linear-gradient(135deg,#8A6FD6,#6345B5);color:#fff;text-align:center;padding:14px;border-radius:12px;font-weight:700;font-size:15px;text-decoration:none;transition:.2s;box-shadow:0 4px 20px rgba(138,111,214,.35);">
                    <i class="fas fa-credit-card me-2"></i>Tiến Hành Thanh Toán
                </a>
                @else
                <a href="{{ route('login') }}"
                   style="display:block;background:linear-gradient(135deg,#8A6FD6,#6345B5);color:#fff;text-align:center;padding:14px;border-radius:12px;font-weight:700;font-size:15px;text-decoration:none;box-shadow:0 4px 20px rgba(138,111,214,.35);">
                    <i class="fas fa-sign-in-alt me-2"></i>Đăng Nhập Để Thanh Toán
                </a>
                @endauth

                <a href="{{ route('products.index') }}"
                   style="display:block;text-align:center;margin-top:12px;color:#888;font-size:13px;text-decoration:none;">
                    <i class="fas fa-arrow-left me-1"></i>Tiếp tục mua sắm
                </a>
            </div>
        </div>
    </div>

    @endif
</div>
@endsection

@section('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

function updateQty(id, delta) {
    const input = document.getElementById(`qty-${id}`);
    const newQty = Math.max(1, parseInt(input.value) + delta);
    input.value = newQty;
    updateQtyDirect(id, newQty);
}

function updateQtyDirect(id, qty) {
    fetch(`/gio-hang/cap-nhat/${id}`, {
        method: 'PATCH',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken},
        body: JSON.stringify({quantity: parseInt(qty)})
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            document.getElementById(`subtotal-${id}`).textContent = res.item_subtotal;
            const subtotalEl = document.getElementById('cart-subtotal');
            const totalEl = document.getElementById('cart-total');
            if (subtotalEl) subtotalEl.textContent = res.cart_subtotal;
            if (totalEl) totalEl.textContent = res.total;
            document.getElementById('cartCount').textContent = res.cart_count;
        } else {
            alert(res.message);
        }
    });
}

function removeItem(id) {
    if (!confirm('Xóa sản phẩm này khỏi giỏ hàng?')) return;
    fetch(`/gio-hang/xoa/${id}`, {
        method: 'DELETE',
        headers: {'X-CSRF-TOKEN':csrfToken}
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            document.getElementById(`cart-item-${id}`).remove();
            document.getElementById('cartCount').textContent = res.cart_count;
            if (res.cart_count == 0) location.reload();
        }
    });
}
</script>
@endsection
