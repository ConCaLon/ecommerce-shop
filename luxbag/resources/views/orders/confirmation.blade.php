@extends('layouts.app')

@section('title', 'Đặt Hàng Thành Công #'.$order->order_code)

@section('content')
<div class="container py-5" style="max-width:700px;">

    {{-- ─── Success Banner ─── --}}
    <div style="text-align:center;padding:48px 32px;background:#fff;border-radius:20px;box-shadow:0 4px 30px rgba(123,94,201,.08);border:1px solid #EAE1F8;margin-bottom:28px;">
        <div style="width:80px;height:80px;background:linear-gradient(135deg,#27AE60,#2ECC71);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;box-shadow:0 4px 20px rgba(39,174,96,.3);">
            <i class="fas fa-check" style="color:#fff;font-size:36px;"></i>
        </div>
        <h1 style="font-size:26px;font-weight:800;color:#2D243D;margin-bottom:8px;">Đặt Hàng Thành Công!</h1>
        <p style="color:#888;font-size:14px;margin-bottom:4px;">Cảm ơn bạn đã tin tưởng Bloom & Bold</p>
        <p style="color:#888;font-size:14px;">Chúng tôi sẽ liên hệ xác nhận đơn hàng qua email và điện thoại sớm nhất.</p>

        <div style="margin-top:24px;display:inline-flex;align-items:center;gap:8px;background:rgba(138,111,214,.1);border:1px solid rgba(138,111,214,.25);border-radius:12px;padding:12px 24px;">
            <span style="color:#666;font-size:13px;">Mã đơn hàng:</span>
            <span style="font-family:monospace;font-size:18px;font-weight:800;color:#6345B5;letter-spacing:1px;">{{ $order->order_code }}</span>
        </div>

        {{-- MoMo Success Notification --}}
        @if(session('momo_success'))
        <div style="margin-top:20px;background:linear-gradient(135deg,#FFF0F7,#FFF5FA);border:1px solid #F8BBD0;border-radius:12px;padding:14px 24px;display:inline-flex;align-items:center;gap:10px;">
            <div style="width:36px;height:36px;background:linear-gradient(135deg,#ae2070,#d82d8b);border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="fas fa-check" style="color:#fff;font-size:14px;"></i>
            </div>
            <div style="text-align:left;">
                <div style="font-weight:700;font-size:14px;color:#ae2070;">Thanh toán MoMo thành công!</div>
                <div style="font-size:12px;color:#888;">Đơn hàng đã được thanh toán qua ví MoMo</div>
            </div>
        </div>
        @endif

        {{-- MoMo Error Notification --}}
        @if(session('momo_error'))
        <div style="margin-top:20px;background:rgba(231,76,60,.06);border:1px solid rgba(231,76,60,.2);border-radius:12px;padding:14px 24px;">
            <div style="font-weight:700;font-size:14px;color:#E74C3C;margin-bottom:4px;">
                <i class="fas fa-exclamation-triangle me-2"></i>Thanh toán MoMo chưa hoàn tất
            </div>
            <div style="font-size:13px;color:#666;">{{ session('momo_error') }}</div>
            <div style="font-size:12px;color:#888;margin-top:6px;">Bạn có thể liên hệ hỗ trợ hoặc chọn phương thức thanh toán khác.</div>
        </div>
        @endif
    </div>

    {{-- ─── Order Info ─── --}}
    <div style="background:#fff;border-radius:16px;box-shadow:0 2px 20px rgba(123,94,201,.06);border:1px solid #EAE1F8;padding:28px;margin-bottom:20px;">
        <h5 style="font-weight:700;margin-bottom:20px;padding-bottom:14px;border-bottom:1px solid #F3EDFB;">Thông Tin Đơn Hàng</h5>

        <div class="row g-3">
            <div class="col-sm-6">
                <div style="font-size:12px;color:#888;margin-bottom:4px;text-transform:uppercase;letter-spacing:.5px;">Khách hàng</div>
                <div style="font-weight:700;">{{ $order->shipping_name }}</div>
                <div style="font-size:13px;color:#666;">{{ $order->shipping_phone }}</div>
                <div style="font-size:13px;color:#666;">{{ $order->shipping_email }}</div>
            </div>
            <div class="col-sm-6">
                <div style="font-size:12px;color:#888;margin-bottom:4px;text-transform:uppercase;letter-spacing:.5px;">Địa chỉ giao hàng</div>
                <div style="font-size:13.5px;line-height:1.7;">{{ $order->shipping_address }}</div>
            </div>
            <div class="col-sm-6">
                <div style="font-size:12px;color:#888;margin-bottom:4px;text-transform:uppercase;letter-spacing:.5px;">Phương thức thanh toán</div>
                <div style="font-weight:700;">{{ $order->payment_method_label }}</div>

                {{-- Bank Transfer Info --}}
                @if($order->payment_method === 'bank_transfer')
                <div style="margin-top:16px;background:#F7F2FD;border:1px solid #EAE0FA;border-radius:12px;padding:20px;">
                    <div style="font-size:14px;font-weight:700;margin-bottom:12px;color:#6345B5;display:flex;align-items:center;gap:8px;">
                        <i class="fas fa-university"></i>Thông tin chuyển khoản ngân hàng
                    </div>
                    <div style="display:flex;flex-wrap:wrap;gap:20px;align-items:center;">
                        <div style="flex:1;min-width:200px;">
                            <div style="font-size:13px;margin-bottom:6px;color:#555;">Ngân hàng: <strong style="color:#2D243D;">Techcombank</strong></div>
                            <div style="font-size:13px;margin-bottom:6px;color:#555;">Số tài khoản (STK): <strong style="color:#2D243D;font-size:14px;">1010 1812 2005</strong></div>
                            <div style="font-size:13px;margin-bottom:6px;color:#555;">Chủ tài khoản: <strong style="color:#2D243D;text-transform:uppercase;">AN CONG CHUAN</strong></div>
                            <div style="font-size:13px;margin-bottom:6px;color:#555;">Số tiền: <strong style="color:#6345B5;font-size:16px;font-weight:800;">{{ vnd($order->total) }}</strong></div>
                            <div style="font-size:13px;margin-bottom:12px;color:#555;">Nội dung chuyển khoản: <strong style="color:#e74c3c;font-family:monospace;font-size:15px;letter-spacing:0.5px;">{{ $order->order_code }}</strong></div>
                            <div style="font-size:12px;color:#777;line-height:1.5;border-top:1px dashed #EAE0FA;padding-top:8px;">
                                <i class="fas fa-info-circle me-1" style="color:#8A6FD6;"></i>Quét mã QR bằng ứng dụng ngân hàng và <strong>nhập chính xác mã đơn hàng</strong> làm nội dung chuyển khoản để chúng tôi xác nhận giao hàng.
                            </div>
                        </div>
                        <div style="text-align:center;width:150px;margin:0 auto;flex-shrink:0;">
                            <img src="{{ asset('assets/images/techcombank_qr.png') }}" alt="Techcombank QR Code" style="width:140px;height:140px;object-fit:contain;border:3px solid #fff;border-radius:12px;box-shadow:0 4px 15px rgba(0,0,0,0.08);">
                            <div style="font-size:11px;color:#888;margin-top:6px;font-weight:600;">Mã QR chuyển khoản</div>
                        </div>
                    </div>
                </div>
                @endif

                {{-- MoMo Payment Info --}}
                @if($order->payment_method === 'momo')
                <div style="margin-top:16px;background:linear-gradient(135deg,#FFF0F7,#FFF5FA);border:1px solid #F8BBD0;border-radius:12px;padding:20px;">
                    <div style="font-size:14px;font-weight:700;margin-bottom:12px;color:#ae2070;display:flex;align-items:center;gap:8px;">
                        <i class="fas fa-mobile-alt"></i>Thanh toán qua MoMo
                    </div>
                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
                        <span style="font-size:13px;color:#555;">Trạng thái:</span>
                        @if($order->payment_status === 'paid')
                        <span style="padding:4px 12px;border-radius:20px;font-size:12px;font-weight:700;background:rgba(39,174,96,.1);color:#27AE60;">
                            <i class="fas fa-check-circle me-1"></i>Đã thanh toán
                        </span>
                        @elseif($order->payment_status === 'failed')
                        <span style="padding:4px 12px;border-radius:20px;font-size:12px;font-weight:700;background:rgba(231,76,60,.1);color:#E74C3C;">
                            <i class="fas fa-times-circle me-1"></i>Thanh toán thất bại
                        </span>
                        @else
                        <span style="padding:4px 12px;border-radius:20px;font-size:12px;font-weight:700;background:rgba(243,156,18,.1);color:#F39C12;">
                            <i class="fas fa-clock me-1"></i>Chờ thanh toán
                        </span>
                        @endif
                    </div>
                    @if($order->payment?->transaction_id)
                    <div style="font-size:13px;color:#555;">
                        Mã giao dịch: <strong style="font-family:monospace;color:#ae2070;">{{ $order->payment->transaction_id }}</strong>
                    </div>
                    @endif
                    <div style="font-size:13px;color:#555;margin-top:4px;">
                        Số tiền: <strong style="color:#ae2070;font-size:16px;font-weight:800;">{{ vnd($order->total) }}</strong>
                    </div>
                </div>
                @endif
            </div>
            <div class="col-sm-6">
                <div style="font-size:12px;color:#888;margin-bottom:4px;text-transform:uppercase;letter-spacing:.5px;">Vận chuyển</div>
                @if($order->ghn_district_id)
                <div style="display:flex;align-items:center;gap:8px;">
                    <img src="https://file.hstatic.net/200000472237/file/ghn_logo_ee4e8c4dba964a3a9cf71e3996060298.png" alt="GHN" style="height:20px;object-fit:contain;" onerror="this.style.display='none'">
                    <span style="font-weight:700;">Giao Hàng Nhanh</span>
                </div>
                @else
                <div style="font-weight:700;">{{ $order->shippingMethod?->name ?? 'Tiêu chuẩn' }}</div>
                @if($order->shippingMethod?->estimated_days)
                <div style="font-size:13px;color:#888;"><i class="fas fa-clock me-1"></i>{{ $order->shippingMethod->estimated_days }}</div>
                @endif
                @endif
                <div style="font-size:13px;color:#888;margin-top:4px;">Phí: {{ $order->shipping_fee > 0 ? vnd($order->shipping_fee) : 'Miễn phí' }}</div>
            </div>
        </div>
    </div>

    {{-- ─── Products ─── --}}
    <div style="background:#fff;border-radius:16px;box-shadow:0 2px 20px rgba(123,94,201,.06);border:1px solid #EAE1F8;padding:28px;margin-bottom:20px;">
        <h5 style="font-weight:700;margin-bottom:20px;padding-bottom:14px;border-bottom:1px solid #F3EDFB;">Sản Phẩm Đặt ({{ $order->details->count() }})</h5>
        @foreach($order->details as $detail)
        <div style="display:flex;gap:14px;align-items:center;padding:12px 0;border-bottom:1px solid #F3EDFB;">
            <div style="font-size:14px;font-weight:700;color:#888;">{{ $loop->iteration }}</div>
            <div class="flex-1">
                <div style="font-weight:600;font-size:13.5px;">{{ $detail->product_name }}</div>
                @if($detail->variant_info)<div style="font-size:12px;color:#888;">{{ $detail->variant_info }}</div>@endif
                <div style="font-size:12px;color:#aaa;">x{{ $detail->quantity }} × {{ vnd($detail->unit_price) }}</div>
            </div>
            <div style="font-weight:700;color:#2D243D;">{{ vnd($detail->subtotal) }}</div>
        </div>
        @endforeach

        <div style="margin-top:16px;padding-top:16px;">
            <div class="d-flex justify-content-between mb-2">
                <span style="color:#888;">Tạm tính</span>
                <span>{{ vnd($order->subtotal) }}</span>
            </div>
            @if($order->discount_amount > 0)
            <div class="d-flex justify-content-between mb-2">
                <span style="color:#888;">Giảm giá ({{ $order->coupon_code }})</span>
                <span style="color:#27AE60;">-{{ vnd($order->discount_amount) }}</span>
            </div>
            @endif
            <div class="d-flex justify-content-between mb-2">
                <span style="color:#888;">Phí vận chuyển</span>
                <span>{{ $order->shipping_fee > 0 ? vnd($order->shipping_fee) : 'Miễn phí' }}</span>
            </div>
            <div class="d-flex justify-content-between" style="border-top:2px solid #F3EDFB;padding-top:12px;margin-top:8px;">
                <span style="font-weight:800;font-size:16px;">Tổng cộng</span>
                <span style="font-weight:800;font-size:20px;color:#6345B5;">{{ vnd($order->total) }}</span>
            </div>
        </div>
    </div>

    {{-- ─── Actions ─── --}}
    <div class="d-flex gap-3 justify-content-center">
        <a href="{{ route('orders.show', $order->order_code) }}"
           style="padding:12px 28px;background:linear-gradient(135deg,#8A6FD6,#6345B5);color:#fff;border-radius:12px;font-weight:700;text-decoration:none;font-size:14px;box-shadow:0 4px 20px rgba(138,111,214,.35);">
            <i class="fas fa-eye me-2"></i>Xem Đơn Hàng
        </a>
        <a href="{{ route('home') }}"
           style="padding:12px 28px;background:#fff;color:#2D243D;border:1.5px solid #EAE1F8;border-radius:12px;font-weight:700;text-decoration:none;font-size:14px;">
            <i class="fas fa-home me-2"></i>Về Trang Chủ
        </a>
    </div>

</div>
@endsection
