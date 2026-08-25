@extends('layouts.app')

@section('title', 'Liên Hệ — Bloom & Bold')
@section('meta_description', 'Liên hệ với Bloom & Bold để được tư vấn và hỗ trợ.')

@section('content')
<div class="container py-5">
    <div style="text-align:center;margin-bottom:48px;">
        <h1 style="font-family:'Playfair Display',serif;font-size:40px;margin-bottom:12px;">Liên Hệ Chúng Tôi</h1>
        <p style="color:#888;font-size:16px;max-width:500px;margin:0 auto;">Chúng tôi luôn sẵn sàng lắng nghe và hỗ trợ bạn 24/7</p>
    </div>

    <div class="row g-5">
        {{-- ─── Contact Info ─── --}}
        <div class="col-lg-4">
            @foreach([
                ['fa-map-marker-alt','Địa Chỉ','123 Đường Nguyễn Huệ, Quận 1, TP. Hồ Chí Minh'],
                ['fa-phone','Điện Thoại', setting('site_phone', '+84 377277542')],
                ['fa-envelope','Email','hello@bloomandbold.vn'],
                ['fa-clock','Giờ Làm Việc','Thứ 2 – Thứ 7: 8:00 – 20:00'],
            ] as $info)
            <div style="display:flex;gap:16px;align-items:flex-start;padding:20px;background:#fff;border-radius:14px;box-shadow:0 2px 12px rgba(123,94,201,.06);margin-bottom:16px;border:1px solid #EAE1F8;">
                <div style="width:48px;height:48px;border-radius:50%;background:rgba(138,111,214,.15);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="fas {{ $info[0] }}" style="color:#8A6FD6;font-size:18px;"></i>
                </div>
                <div>
                    <div style="font-weight:700;font-size:14px;margin-bottom:4px;">{{ $info[1] }}</div>
                    <div style="font-size:13.5px;color:#666;line-height:1.7;">{{ $info[2] }}</div>
                </div>
            </div>
            @endforeach

            {{-- Social --}}
            <div style="display:flex;gap:12px;margin-top:8px;">
                @foreach([['fab fa-facebook-f','#1877F2'],['fab fa-instagram','#E1306C'],['fab fa-tiktok','#000'],['fab fa-youtube','#FF0000']] as $social)
                <a href="#" style="width:40px;height:40px;border-radius:50%;background:rgba(138,111,214,.1);display:flex;align-items:center;justify-content:center;color:{{ $social[1] }};font-size:16px;text-decoration:none;transition:.2s;"
                   onmouseover="this.style.background='{{ $social[1] }}';this.style.color='#fff'"
                   onmouseout="this.style.background='rgba(138,111,214,.1)';this.style.color='{{ $social[1] }}'">
                    <i class="{{ $social[0] }}"></i>
                </a>
                @endforeach
            </div>
        </div>

        {{-- ─── Contact Form ─── --}}
        <div class="col-lg-8">
            <div style="background:#fff;border-radius:20px;box-shadow:0 2px 20px rgba(123,94,201,.06);border:1px solid #EAE1F8;padding:36px;">
                <h4 style="font-weight:700;margin-bottom:8px;">Gửi Tin Nhắn Cho Chúng Tôi</h4>
                <p style="color:#888;font-size:14px;margin-bottom:28px;">Chúng tôi sẽ phản hồi trong vòng 24 giờ làm việc</p>

                @if(session('success'))
                <div class="alert alert-success rounded-3 mb-4">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                </div>
                @endif
                @if($errors->any())
                <div style="background:rgba(231,76,60,.1);border:1px solid rgba(231,76,60,.2);border-radius:10px;padding:14px 18px;margin-bottom:20px;color:#E74C3C;">
                    <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
                @endif

                <form method="POST" action="{{ route('pages.contact.send') }}">
                    @csrf
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label style="font-size:13px;font-weight:600;display:block;margin-bottom:6px;">Họ và tên <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required placeholder="Nguyễn Văn A"
                                   value="{{ old('name', auth()->user()?->name) }}"
                                   style="border-radius:10px;border:1.5px solid #E2D5F7;font-size:14px;padding:11px 14px;">
                        </div>
                        <div class="col-md-6">
                            <label style="font-size:13px;font-weight:600;display:block;margin-bottom:6px;">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" required placeholder="email@example.com"
                                   value="{{ old('email', auth()->user()?->email) }}"
                                   style="border-radius:10px;border:1.5px solid #E2D5F7;font-size:14px;padding:11px 14px;">
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label style="font-size:13px;font-weight:600;display:block;margin-bottom:6px;">Số điện thoại</label>
                            <input type="tel" name="phone" class="form-control" placeholder="0912 345 678"
                                   value="{{ old('phone', auth()->user()?->phone) }}"
                                   style="border-radius:10px;border:1.5px solid #E2D5F7;font-size:14px;padding:11px 14px;">
                        </div>
                        <div class="col-md-6">
                            <label style="font-size:13px;font-weight:600;display:block;margin-bottom:6px;">Mã đơn hàng (nếu có)</label>
                            <input type="text" name="order_code" class="form-control" placeholder="VD: LB-123456"
                                   value="{{ old('order_code') }}"
                                   style="border-radius:10px;border:1.5px solid #E2D5F7;font-size:14px;padding:11px 14px;">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label style="font-size:13px;font-weight:600;display:block;margin-bottom:6px;">Chủ đề <span class="text-danger">*</span></label>
                        <select name="subject" class="form-select" style="border-radius:10px;border:1.5px solid #E2D5F7;font-size:14px;padding:11px 14px;">
                            <option value="">— Chọn chủ đề —</option>
                            <option value="Tư vấn sản phẩm" {{ old('subject') == 'Tư vấn sản phẩm' ? 'selected' : '' }}>Tư vấn sản phẩm</option>
                            <option value="Đổi trả hàng" {{ old('subject') == 'Đổi trả hàng' ? 'selected' : '' }}>Đổi trả hàng</option>
                            <option value="Khiếu nại đơn hàng" {{ old('subject') == 'Khiếu nại đơn hàng' ? 'selected' : '' }}>Khiếu nại đơn hàng</option>
                            <option value="Hợp tác kinh doanh" {{ old('subject') == 'Hợp tác kinh doanh' ? 'selected' : '' }}>Hợp tác kinh doanh</option>
                            <option value="Khác" {{ old('subject') == 'Khác' ? 'selected' : '' }}>Khác</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label style="font-size:13px;font-weight:600;display:block;margin-bottom:6px;">Nội dung <span class="text-danger">*</span></label>
                        <textarea name="message" rows="5" class="form-control" required placeholder="Nhập nội dung tin nhắn..."
                                  style="border-radius:10px;border:1.5px solid #E2D5F7;font-size:14px;padding:11px 14px;resize:vertical;">{{ old('message') }}</textarea>
                    </div>
                    <button type="submit"
                        style="background:linear-gradient(135deg,#8A6FD6,#6345B5);color:#fff;border:none;padding:14px 32px;border-radius:12px;font-weight:700;font-size:15px;cursor:pointer;transition:.2s;box-shadow:0 4px 20px rgba(138,111,214,.35);"
                        onmouseover="this.style.transform='translateY(-2px)'"
                        onmouseout="this.style.transform='none'">
                        <i class="fas fa-paper-plane me-2"></i>Gửi Tin Nhắn
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
