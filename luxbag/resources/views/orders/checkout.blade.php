@extends('layouts.app')

@section('title', 'Thanh Toán')
@section('meta_description', 'Hoàn tất đơn hàng của bạn tại Bloom & Bold.')

@section('head')
<style>
.checkout-section { background:#fff; border-radius:16px; box-shadow:0 2px 20px rgba(123,94,201,.06); border:1px solid #EAE1F8; padding:28px; margin-bottom:24px; }
.checkout-section h5 { font-weight:700; font-size:16px; margin-bottom:20px; padding-bottom:14px; border-bottom:1px solid #F3EDFB; }
.form-field label { font-size:13px; font-weight:600; color:#444; margin-bottom:6px; display:block; }
.form-field input, .form-field select, .form-field textarea { border-radius:10px; border:1.5px solid #E2D5F7; font-size:14px; padding:11px 14px; width:100%; transition:.2s; }
.form-field input:focus, .form-field select:focus { border-color:#8A6FD6; box-shadow:0 0 0 3px rgba(138,111,214,.2); outline:none; }
.shipping-option { border:2px solid #EAE1F8; border-radius:12px; padding:14px 18px; cursor:pointer; transition:.2s; margin-bottom:10px; }
.shipping-option:hover, .shipping-option.selected { border-color:#8A6FD6; background:rgba(138,111,214,.06); }
.payment-option { border:2px solid #EAE1F8; border-radius:12px; padding:14px 18px; cursor:pointer; transition:.2s; margin-bottom:10px; display:flex; align-items:center; gap:14px; }
.payment-option:hover, .payment-option.selected { border-color:#8A6FD6; background:rgba(138,111,214,.06); }
.order-item { display:flex; gap:12px; align-items:center; padding:12px 0; border-bottom:1px solid #F3EDFB; }
</style>
@endsection

@section('content')
<div class="container py-5">
    <nav style="font-size:13px;color:#888;margin-bottom:24px;">
        <a href="{{ route('cart.index') }}" style="color:#888;text-decoration:none;">Giỏ hàng</a>
        <i class="fas fa-chevron-right mx-2" style="font-size:10px;"></i>
        <strong style="color:#2D243D;">Thanh toán</strong>
    </nav>

    <h1 class="h3 fw-700 mb-4">Thanh Toán</h1>

    @if($errors->any())
    <div class="alert alert-danger mb-4 rounded-3">
        <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form method="POST" action="{{ route('orders.store') }}" id="checkoutForm">
        @csrf

        <div class="row g-4">
            {{-- ─── Left Column ─── --}}
            <div class="col-lg-7">
                {{-- Shipping Info --}}
                <div class="checkout-section">
                    <h5><i class="fas fa-map-marker-alt me-2" style="color:#8A6FD6;"></i>Thông Tin Giao Hàng</h5>

                    {{-- Saved Addresses --}}
                    @if($addresses->count() > 0)
                    <div style="margin-bottom:20px;">
                        <label style="font-size:13px;font-weight:600;color:#444;display:block;margin-bottom:10px;">Địa chỉ đã lưu</label>
                        @foreach($addresses as $addr)
                        <div class="address-card" style="border:2px solid #EAE1F8;border-radius:10px;padding:14px;cursor:pointer;margin-bottom:8px;transition:.2s;"
                             onclick="fillAddress({{ $addr->id }}, '{{ addslashes($addr->full_name) }}', '{{ $addr->phone }}', '{{ addslashes($addr->province) }}', '{{ addslashes($addr->district) }}', '{{ addslashes($addr->ward) }}', '{{ addslashes($addr->address_detail) }}')"
                             id="addr-card-{{ $addr->id }}">
                            <div style="display:flex;align-items:center;gap:10px;">
                                @if($addr->is_default)
                                <span style="font-size:10px;background:rgba(138,111,214,.15);color:#8A6FD6;padding:2px 8px;border-radius:4px;font-weight:700;">Mặc định</span>
                                @endif
                                <strong style="font-size:13.5px;">{{ $addr->full_name }}</strong>
                                <span style="color:#888;font-size:13px;">{{ $addr->phone }}</span>
                            </div>
                            <div style="font-size:12px;color:#888;margin-top:4px;">{{ $addr->address_detail }}, {{ $addr->ward }}, {{ $addr->district }}, {{ $addr->province }}</div>
                        </div>
                        @endforeach
                        <button type="button" onclick="clearAddressForm()" style="background:none;border:none;font-size:13px;color:#8A6FD6;cursor:pointer;font-weight:600;">
                            <i class="fas fa-plus me-1"></i>Nhập địa chỉ mới
                        </button>
                    </div>
                    @endif

                    <div class="row g-3">
                        <div class="col-md-6 form-field">
                            <label>Họ và tên người nhận <span class="text-danger">*</span></label>
                            <input type="text" name="shipping_name" id="field-name" required value="{{ old('shipping_name', auth()->user()->name) }}" placeholder="Nguyễn Văn A">
                        </div>
                        <div class="col-md-6 form-field">
                            <label>Số điện thoại <span class="text-danger">*</span></label>
                            <input type="tel" name="shipping_phone" id="field-phone" required value="{{ old('shipping_phone', auth()->user()->phone) }}" placeholder="0912 345 678">
                        </div>
                        <div class="col-md-4 form-field">
                            <label>Tỉnh / Thành phố <span class="text-danger">*</span></label>
                            <input type="text" name="shipping_province" id="field-province" required value="{{ old('shipping_province') }}" placeholder="TP. Hồ Chí Minh">
                        </div>
                        <div class="col-md-4 form-field">
                            <label>Quận / Huyện <span class="text-danger">*</span></label>
                            <input type="text" name="shipping_district" id="field-district" required value="{{ old('shipping_district') }}" placeholder="Quận 1">
                        </div>
                        <div class="col-md-4 form-field">
                            <label>Phường / Xã <span class="text-danger">*</span></label>
                            <input type="text" name="shipping_ward" id="field-ward" required value="{{ old('shipping_ward') }}" placeholder="Phường Bến Nghé">
                        </div>
                        <div class="col-12 form-field">
                            <label>Địa chỉ chi tiết <span class="text-danger">*</span></label>
                            <input type="text" name="shipping_address_detail" id="field-address" required value="{{ old('shipping_address_detail') }}" placeholder="Số 123 đường ABC...">
                        </div>
                        <div class="col-12 form-field">
                            <label>Ghi chú đơn hàng (tùy chọn)</label>
                            <textarea name="notes" rows="2" placeholder="Ghi chú về đơn hàng, ví dụ: thời gian giao hàng...">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Shipping Method --}}
                <div class="checkout-section">
                    <h5><i class="fas fa-truck me-2" style="color:#8A6FD6;"></i>Phương Thức Vận Chuyển</h5>
                    @foreach($shippingMethods as $method)
                    <label class="shipping-option {{ $loop->first ? 'selected' : '' }}" onclick="selectShipping(this, {{ $method->id }}, {{ $method->price }})">
                        <input type="radio" name="shipping_method_id" value="{{ $method->id }}" {{ $loop->first ? 'checked' : '' }} style="display:none;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div style="font-weight:700;font-size:14px;">{{ $method->name }}</div>
                                @if($method->estimated_days)
                                <div style="font-size:12px;color:#888;margin-top:2px;"><i class="fas fa-clock me-1"></i>{{ $method->estimated_days }}</div>
                                @endif
                                @if($method->description)
                                <div style="font-size:12px;color:#888;">{{ $method->description }}</div>
                                @endif
                            </div>
                            <div style="font-weight:700;font-size:14px;color:#6345B5;">
                                {{ $method->price > 0 ? vnd($method->price) : 'Miễn phí' }}
                            </div>
                        </div>
                    </label>
                    @endforeach
                </div>

                {{-- Payment Method --}}
                <div class="checkout-section">
                    <h5><i class="fas fa-credit-card me-2" style="color:#8A6FD6;"></i>Phương Thức Thanh Toán</h5>
                    <label class="payment-option selected" onclick="selectPayment(this, 'cod')">
                        <input type="radio" name="payment_method" value="cod" checked style="display:none;">
                        <img src="{{ asset('assets/images/cod-icon.png') }}" alt="" style="width:40px;height:40px;object-fit:contain;" onerror="this.style.display='none'">
                        <div style="font-size:30px;">💵</div>
                        <div>
                            <div style="font-weight:700;font-size:14px;">Thanh toán khi nhận hàng (COD)</div>
                            <div style="font-size:12px;color:#888;">Trả tiền mặt khi nhận được hàng</div>
                        </div>
                    </label>
                    <label class="payment-option" onclick="selectPayment(this, 'bank_transfer')">
                        <input type="radio" name="payment_method" value="bank_transfer" style="display:none;">
                        <div style="font-size:30px;">🏦</div>
                        <div>
                            <div style="font-weight:700;font-size:14px;">Chuyển khoản ngân hàng</div>
                            <div style="font-size:12px;color:#888;">Chuyển khoản trước khi giao hàng</div>
                        </div>
                    </label>

                    <div id="bank-transfer-info" style="display:none; margin-top:12px; background:#F7F2FD; border:1px solid #EAE0FA; border-radius:10px; padding:16px; font-size:13.5px; transition: .3s ease-in-out;">
                        <div style="font-weight:700; margin-bottom:8px; color:#6345B5;"><i class="fas fa-university me-2"></i>Thông tin tài khoản ngân hàng:</div>
                        <div style="margin-bottom:4px;">Ngân hàng: <strong>Techcombank</strong></div>
                        <div style="margin-bottom:4px;">Số tài khoản: <strong>1010 1812 2005</strong></div>
                        <div style="margin-bottom:4px;">Chủ tài khoản: <strong>AN CONG CHUAN</strong></div>
                        <div style="font-size:12px; color:#666; margin-top:10px; border-top: 1px dashed #EAE0FA; padding-top: 8px;"><i class="fas fa-info-circle me-1"></i>Sau khi đặt hàng thành công, hệ thống sẽ hiển thị mã QR và mã đơn hàng cụ thể để bạn thực hiện chuyển khoản.</div>
                    </div>
                </div>
            </div>

            {{-- ─── Right Column (Order Summary) ─── --}}
            <div class="col-lg-5">
                <div style="position:sticky;top:90px;">
                    <div class="checkout-section">
                        <h5>Đơn Hàng ({{ $cartItems->count() }} sản phẩm)</h5>

                        @foreach($cartItems as $item)
                        <div class="order-item">
                            <div style="position:relative;">
                                @if($item->product->primaryImage)
                                    <img src="{{ $item->product->primaryImage->url }}" alt=""
                                         style="width:54px;height:54px;border-radius:8px;object-fit:cover;border:1px solid #eee;">
                                @else
                                    <div style="width:54px;height:54px;border-radius:8px;background:#f5f5f5;"></div>
                                @endif
                                <span style="position:absolute;top:-6px;right:-6px;background:linear-gradient(135deg,#8A6FD6,#6345B5);color:#fff;font-size:11px;font-weight:700;width:20px;height:20px;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                                    {{ $item->quantity }}
                                </span>
                            </div>
                            <div class="flex-1">
                                <div style="font-size:13.5px;font-weight:600;">{{ $item->product->name }}</div>
                                @if($item->variant)<div style="font-size:12px;color:#888;">{{ $item->variant->label }}</div>@endif
                            </div>
                            <div style="font-weight:700;font-size:14px;white-space:nowrap;">{{ vnd($item->subtotal) }}</div>
                        </div>
                        @endforeach

                        <div style="margin-top:16px;padding-top:16px;border-top:1px solid #F3EDFB;">
                            <div class="d-flex justify-content-between mb-2">
                                <span style="color:#888;">Tạm tính</span>
                                <span style="font-weight:600;" id="summary-subtotal">{{ vnd($subtotal) }}</span>
                            </div>
                            @if($discount > 0)
                            <div class="d-flex justify-content-between mb-2">
                                <span style="color:#888;">Giảm giá</span>
                                <span style="font-weight:600;color:#27AE60;">-{{ vnd($discount) }}</span>
                            </div>
                            @endif
                            <div class="d-flex justify-content-between mb-2">
                                <span style="color:#888;">Phí vận chuyển</span>
                                <span id="shipping-fee-display" style="font-weight:600;">{{ $shippingMethods->first() ? vnd($shippingMethods->first()->price) : '0đ' }}</span>
                            </div>
                            <div style="border-top:2px solid #F3EDFB;padding-top:12px;margin-top:8px;" class="d-flex justify-content-between">
                                <span style="font-weight:700;font-size:16px;">Tổng cộng</span>
                                <span id="summary-total" style="font-weight:800;font-size:20px;color:#6345B5;">{{ vnd($subtotal - $discount + ($shippingMethods->first()?->price ?? 0)) }}</span>
                            </div>
                        </div>

                        @if($coupon)
                        <div style="background:rgba(39,174,96,.08);border:1px solid rgba(39,174,96,.2);border-radius:8px;padding:10px 14px;margin-top:16px;">
                            <i class="fas fa-tag me-2" style="color:#27AE60;"></i>
                            <strong style="color:#27AE60;">{{ $coupon->code }}</strong>
                            <span style="color:#888;font-size:13px;margin-left:4px;">đã áp dụng</span>
                        </div>
                        @endif

                        <button type="submit" style="display:block;width:100%;background:linear-gradient(135deg,#8A6FD6,#6345B5);color:#fff;border:none;padding:15px;border-radius:12px;font-weight:800;font-size:15px;cursor:pointer;margin-top:20px;transition:.2s;box-shadow:0 4px 20px rgba(138,111,214,.35);"
                            onmouseover="this.style.transform='translateY(-2px)'"
                            onmouseout="this.style.transform='none'">
                            <i class="fas fa-shield-alt me-2"></i>Đặt Hàng Ngay
                        </button>

                        <div style="text-align:center;margin-top:12px;font-size:12px;color:#888;">
                            <i class="fas fa-lock me-1"></i>Thông tin được mã hóa và bảo mật
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
const subtotal = {{ $subtotal }};
const discount = {{ $discount }};

function selectShipping(el, id, price) {
    document.querySelectorAll('.shipping-option').forEach(o => o.classList.remove('selected'));
    el.classList.add('selected');
    el.querySelector('input').checked = true;

    const feeFormatted = price > 0 ? new Intl.NumberFormat('vi-VN').format(price) + 'đ' : 'Miễn phí';
    document.getElementById('shipping-fee-display').textContent = feeFormatted;
    const total = subtotal - discount + price;
    document.getElementById('summary-total').textContent = new Intl.NumberFormat('vi-VN').format(total) + 'đ';
}

function selectPayment(el, method) {
    document.querySelectorAll('.payment-option').forEach(o => o.classList.remove('selected'));
    el.classList.add('selected');
    el.querySelector('input').checked = true;

    const bankInfo = document.getElementById('bank-transfer-info');
    if (method === 'bank_transfer') {
        bankInfo.style.display = 'block';
    } else {
        bankInfo.style.display = 'none';
    }
}

function fillAddress(id, name, phone, province, district, ward, address) {
    document.getElementById('field-name').value = name;
    document.getElementById('field-phone').value = phone;
    document.getElementById('field-province').value = province;
    document.getElementById('field-district').value = district;
    document.getElementById('field-ward').value = ward;
    document.getElementById('field-address').value = address;

    document.querySelectorAll('.address-card').forEach(c => {
        c.style.borderColor = '#EAE1F8';
        c.style.background = '';
    });
    document.getElementById(`addr-card-${id}`).style.borderColor = '#8A6FD6';
    document.getElementById(`addr-card-${id}`).style.background = 'rgba(138,111,214,.06)';
}

function clearAddressForm() {
    ['field-name','field-phone','field-province','field-district','field-ward','field-address'].forEach(id => {
        document.getElementById(id).value = '';
    });
    document.querySelectorAll('.address-card').forEach(c => {
        c.style.borderColor = '#EAE1F8';
        c.style.background = '';
    });
}

// Auto select default address
@if($addresses->count() > 0)
@php $default = $addresses->firstWhere('is_default', true) ?? $addresses->first(); @endphp
window.onload = () => fillAddress(
    {{ $default->id }},
    '{{ addslashes($default->full_name) }}',
    '{{ $default->phone }}',
    '{{ addslashes($default->province) }}',
    '{{ addslashes($default->district) }}',
    '{{ addslashes($default->ward) }}',
    '{{ addslashes($default->address_detail) }}'
);
@endif
</script>
@endsection
