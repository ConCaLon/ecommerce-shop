@extends('layouts.app')

@section('title', 'Chính Sách Đổi Trả — Bloom & Bold')
@section('meta_description', 'Xem chính sách đổi trả và bảo hành sản phẩm tại Bloom & Bold.')

@section('content')
<div class="container py-5" style="max-width:900px;">
    <h1 style="font-family:'Playfair Display',serif;font-size:36px;margin-bottom:8px;">Chính Sách Đổi Trả</h1>
    <p style="color:#888;margin-bottom:40px;">Cập nhật lần cuối: Tháng 1/2025</p>

    @foreach([
        ['1', 'Điều Kiện Đổi Trả', [
            'Sản phẩm còn nguyên tem, nhãn, chưa qua sử dụng',
            'Thời gian đổi trả trong vòng <strong>7 ngày</strong> kể từ ngày nhận hàng',
            'Có hóa đơn mua hàng hoặc mã đơn hàng',
            'Sản phẩm không nằm trong danh mục hàng không đổi trả (hàng giảm giá đặc biệt, hàng khuyến mãi)',
        ]],
        ['2', 'Quy Trình Đổi Trả', [
            'Bước 1: Liên hệ hotline hoặc gửi email để được hướng dẫn',
            'Bước 2: Đóng gói sản phẩm cẩn thận, kèm theo hóa đơn',
            'Bước 3: Gửi hàng về địa chỉ: 123 Đường Nguyễn Huệ, Q.1, TP.HCM',
            'Bước 4: Chúng tôi kiểm tra và xử lý trong vòng 3-5 ngày làm việc',
        ]],
        ['3', 'Các Trường Hợp Được Đổi Trả', [
            'Sản phẩm bị lỗi kỹ thuật do nhà sản xuất',
            'Sản phẩm không đúng mô tả trên website',
            'Sản phẩm bị hư hỏng trong quá trình vận chuyển',
            'Giao sai sản phẩm (màu sắc, kích thước)',
        ]],
        ['4', 'Chi Phí Đổi Trả', [
            'Nếu lỗi do chúng tôi: <strong>Miễn phí</strong> toàn bộ chi phí vận chuyển',
            'Nếu khách hàng đổi ý hoặc chọn sai: Khách hàng chịu phí vận chuyển chiều về',
            'Phí vận chuyển đổi trả sẽ được khấu trừ vào giá trị hoàn tiền (nếu có)',
        ]],
        ['5', 'Chính Sách Bảo Hành', [
            'Bảo hành <strong>24 tháng</strong> cho tất cả sản phẩm',
            'Bảo hành bao gồm: lỗi khóa kéo, đứt chỉ, bong keo, lỗi phụ kiện kim loại',
            'Không bảo hành các hư hỏng do: sử dụng không đúng cách, trầy xước, tiếp xúc với hóa chất, lửa, nước',
            'Mang sản phẩm đến showroom hoặc liên hệ hotline để được hỗ trợ bảo hành',
        ]],
    ] as $section)
    <div style="background:#fff;border-radius:16px;box-shadow:0 2px 16px rgba(123,94,201,.06);padding:28px;margin-bottom:20px;border:1px solid #EAE1F8;">
        <h2 style="font-size:18px;font-weight:700;margin-bottom:16px;color:#2D243D;">
            <span style="color:#8A6FD6;margin-right:8px;">{{ $section[0] }}.</span>{{ $section[1] }}
        </h2>
        <ul style="list-style:none;padding:0;margin:0;">
            @foreach($section[2] as $item)
            <li style="padding:10px 0;border-bottom:1px solid #F3EDFB;font-size:14.5px;color:#555;display:flex;gap:10px;align-items:flex-start;">
                <i class="fas fa-check-circle" style="color:#8A6FD6;font-size:14px;margin-top:3px;flex-shrink:0;"></i>
                <span>{!! $item !!}</span>
            </li>
            @endforeach
        </ul>
    </div>
    @endforeach

    <div style="background:rgba(138,111,214,.1);border:1px solid rgba(138,111,214,.25);border-radius:12px;padding:20px;text-align:center;">
        <p style="font-size:14px;color:#666;margin-bottom:12px;">Cần hỗ trợ? Liên hệ với chúng tôi ngay!</p>
        <div class="d-flex gap-3 justify-content-center">
            <a href="tel:{{ str_replace([' ', '+'], '', setting('site_phone', '+84 377277542')) }}" style="padding:10px 20px;background:linear-gradient(135deg,#8A6FD6,#6345B5);color:#fff;border-radius:10px;font-weight:700;text-decoration:none;font-size:13px;box-shadow:0 4px 15px rgba(138,111,214,.35);">
                <i class="fas fa-phone me-1"></i>{{ setting('site_phone', '+84 377277542') }}
            </a>
            <a href="{{ route('pages.contact') }}" style="padding:10px 20px;background:#fff;border:1.5px solid #EAE1F8;color:#333;border-radius:10px;font-weight:700;text-decoration:none;font-size:13px;">
                <i class="fas fa-envelope me-1"></i>Gửi email
            </a>
        </div>
    </div>
</div>
@endsection
