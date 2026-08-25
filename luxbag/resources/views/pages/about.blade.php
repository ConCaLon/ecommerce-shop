@extends('layouts.app')

@section('title', 'Giới Thiệu — Bloom & Bold')
@section('meta_description', 'Khám phá câu chuyện đằng sau thương hiệu túi xách cao cấp Bloom & Bold — nơi thời trang gặp gỡ sự tinh tế.')

@section('content')
<div class="container py-5">

    {{-- Hero --}}
    <div style="text-align:center;padding:64px 24px;background:linear-gradient(135deg,#2D1F47,#4D337A);border-radius:20px;margin-bottom:60px;position:relative;overflow:hidden;border:1px solid rgba(184,163,232,.25);">
        <div style="position:absolute;top:-40px;right:-40px;width:200px;height:200px;border-radius:50%;background:rgba(184,163,232,.12);"></div>
        <div style="position:absolute;bottom:-60px;left:-60px;width:280px;height:280px;border-radius:50%;background:rgba(184,163,232,.08);"></div>
        <p style="color:#C4B5FD;font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:3px;margin-bottom:16px;">Về Chúng Tôi</p>
        <h1 style="font-family:'Playfair Display',serif;font-size:clamp(32px,5vw,52px);color:#fff;margin-bottom:20px;">Nghệ Thuật Trong<br><span style="color:#C4B5FD;">Từng Đường Chỉ</span></h1>
        <p style="color:rgba(255,255,255,.8);font-size:16px;max-width:600px;margin:0 auto;line-height:1.8;">
            Bloom & Bold ra đời từ đam mê với nghệ thuật thủ công và khao khát mang đến những chiếc túi xách cao cấp xứng tầm với phong cách của người Việt hiện đại.
        </p>
    </div>

    {{-- Story --}}
    <div class="row g-5 align-items-center mb-5">
        <div class="col-lg-6">
            <p style="color:#8A6FD6;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:2px;margin-bottom:12px;">Câu Chuyện Của Chúng Tôi</p>
            <h2 style="font-family:'Playfair Display',serif;font-size:32px;margin-bottom:20px;color:#2D243D;">Từ Xưởng Nhỏ Đến Thương Hiệu Cao Cấp</h2>
            <p style="color:#666;font-size:15px;line-height:1.9;margin-bottom:16px;">
                Được thành lập vào năm 2018, Bloom & Bold bắt đầu từ một xưởng thủ công nhỏ tại TP.HCM với đội ngũ chỉ 5 người. Chúng tôi tin rằng một chiếc túi xách không chỉ là phụ kiện — đó là lời tuyên ngôn về phong cách sống.
            </p>
            <p style="color:#666;font-size:15px;line-height:1.9;margin-bottom:24px;">
                Ngày nay, với hơn 50 thợ lành nghề và sự tích hợp công nghệ hiện đại, chúng tôi tạo ra những sản phẩm kết hợp hoàn hảo giữa truyền thống thủ công Việt Nam và thiết kế đương đại.
            </p>
            <div class="row g-3">
                @foreach([['6+', 'Năm kinh nghiệm'], ['50+', 'Thợ lành nghề'], ['10K+', 'Khách hàng tin tưởng'], ['500+', 'Mẫu thiết kế']] as $stat)
                <div class="col-6">
                    <div style="text-align:center;padding:16px;background:#FAF7FE;border:1px solid #EAE1F8;border-radius:12px;">
                        <div style="font-size:24px;font-weight:800;color:#6345B5;margin-bottom:4px;">{{ $stat[0] }}</div>
                        <div style="font-size:12px;color:#888;">{{ $stat[1] }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        <div class="col-lg-6">
            <div style="border-radius:20px;overflow:hidden;box-shadow:0 20px 60px rgba(123,94,201,.12);background:linear-gradient(135deg,#8A6FD6,#6345B5);display:flex;align-items:center;justify-content:center;height:400px;">
                <div style="text-align:center;color:#fff;">
                    <i class="fas fa-bag-shopping" style="font-size:80px;opacity:.6;margin-bottom:16px;display:block;"></i>
                    <div style="font-family:'Playfair Display',serif;font-size:36px;font-weight:700;">Bloom & Bold</div>
                    <div style="font-size:13px;opacity:.85;margin-top:4px;">Est. 2018</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Values --}}
    <div style="background:#FAF7FE;border-radius:20px;padding:48px;margin-bottom:60px;border:1px solid #EAE1F8;">
        <div style="text-align:center;margin-bottom:40px;">
            <h2 style="font-family:'Playfair Display',serif;font-size:32px;color:#2D243D;">Giá Trị Cốt Lõi</h2>
        </div>
        <div class="row g-4">
            @foreach([
                ['fa-award','Chất Lượng Tuyệt Đỉnh','Mỗi sản phẩm trải qua 47 bước kiểm tra chất lượng nghiêm ngặt trước khi đến tay khách hàng.'],
                ['fa-leaf','Bền Vững','Chúng tôi sử dụng da thuộc từ các trang trại được chứng nhận và thuốc nhuộm an toàn với môi trường.'],
                ['fa-handshake','Cam Kết','Bảo hành 2 năm cho mọi sản phẩm và hỗ trợ khách hàng 24/7.'],
                ['fa-paint-brush','Sáng Tạo','Đội ngũ thiết kế luôn cập nhật xu hướng toàn cầu và kết hợp tinh hoa văn hóa Việt.'],
            ] as $val)
            <div class="col-md-6 col-lg-3">
                <div style="text-align:center;padding:28px 20px;background:#fff;border-radius:14px;box-shadow:0 2px 12px rgba(123,94,201,.06);height:100%;border:1px solid #EAE1F8;">
                    <div style="width:60px;height:60px;border-radius:50%;background:rgba(138,111,214,.15);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                        <i class="fas {{ $val[0] }}" style="font-size:22px;color:#8A6FD6;"></i>
                    </div>
                    <h5 style="font-weight:700;margin-bottom:10px;font-size:15px;">{{ $val[1] }}</h5>
                    <p style="font-size:13px;color:#888;line-height:1.7;margin:0;">{{ $val[2] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- CTA --}}
    <div style="text-align:center;padding:48px;background:linear-gradient(135deg,#2D1F47,#4D337A);border-radius:20px;color:#fff;border:1px solid rgba(184,163,232,.25);">
        <h2 style="font-family:'Playfair Display',serif;font-size:28px;margin-bottom:16px;">Khám Phá BST Mới Nhất</h2>
        <p style="color:rgba(255,255,255,.8);margin-bottom:24px;">Hơn 500 mẫu túi độc đáo đang chờ bạn</p>
        <a href="{{ route('products.index') }}"
           style="display:inline-block;background:linear-gradient(135deg,#8A6FD6,#6345B5);color:#fff;padding:14px 32px;border-radius:12px;font-weight:700;text-decoration:none;font-size:15px;box-shadow:0 4px 20px rgba(138,111,214,.35);">
            <i class="fas fa-shopping-bag me-2"></i>Mua Sắm Ngay
        </a>
    </div>
</div>
@endsection
