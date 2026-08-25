@extends('layouts.app')

@section('title', 'Câu Hỏi Thường Gặp — Bloom & Bold')
@section('meta_description', 'Giải đáp các thắc mắc thường gặp về sản phẩm, đơn hàng và dịch vụ của Bloom & Bold.')

@section('content')
<div class="container py-5" style="max-width:800px;">
    <div style="text-align:center;margin-bottom:48px;">
        <h1 style="font-family:'Playfair Display',serif;font-size:36px;margin-bottom:12px;">Câu Hỏi Thường Gặp</h1>
        <p style="color:#888;font-size:15px;">Chúng tôi tổng hợp những câu hỏi phổ biến nhất từ khách hàng</p>
    </div>

    @php
    $faqs = [
        'Sản Phẩm' => [
            ['Da sản phẩm của Bloom & Bold có nguồn gốc từ đâu?', 'Chúng tôi sử dụng da bò thật từ các trang trại đạt chuẩn châu Âu, được chứng nhận không sử dụng thuốc nhuộm độc hại. Mỗi sản phẩm đều có chứng chỉ nguồn gốc đi kèm.'],
            ['Làm thế nào để bảo quản túi da đúng cách?', 'Lưu trữ trong túi vải chống bụi đi kèm, tránh ánh nắng trực tiếp, độ ẩm cao. Sau khi dùng, lau sạch nhẹ nhàng bằng khăn mềm khô. Định kỳ 3 tháng/lần dùng kem dưỡng da chuyên dụng.'],
            ['Sản phẩm có bao nhiêu màu sắc và kích thước?', 'Mỗi mẫu túi thường có từ 3-8 màu sắc và 2-3 kích thước khác nhau. Bạn có thể xem chi tiết trên trang sản phẩm.'],
        ],
        'Đặt Hàng & Thanh Toán' => [
            ['Tôi có thể đặt hàng mà không cần tạo tài khoản không?', 'Để đặt hàng, bạn cần đăng ký tài khoản để chúng tôi có thể theo dõi và cập nhật tình trạng đơn hàng cho bạn. Việc đăng ký chỉ mất 1 phút!'],
            ['Các phương thức thanh toán được chấp nhận?', 'Chúng tôi chấp nhận: Thanh toán khi nhận hàng (COD), Chuyển khoản ngân hàng, Ví điện tử MoMo/ZaloPay, Thẻ tín dụng/ghi nợ Visa/Mastercard.'],
            ['Tôi có thể thay đổi hoặc hủy đơn hàng không?', 'Bạn có thể hủy đơn hàng trong vòng 2 giờ sau khi đặt, hoặc trước khi đơn được xác nhận. Vào mục "Đơn hàng của tôi" và chọn "Hủy đơn".'],
        ],
        'Vận Chuyển' => [
            ['Thời gian giao hàng là bao lâu?', 'TP.HCM và Hà Nội: 1-2 ngày làm việc. Các tỉnh khác: 3-5 ngày làm việc. Dịch vụ giao hỏa tốc (nội thành HCM): 2-4 giờ.'],
            ['Phí vận chuyển được tính như thế nào?', 'Miễn phí vận chuyển cho đơn hàng từ 500.000đ. Dưới 500.000đ: Phí 30.000đ nội thành, 40.000đ - 80.000đ tùy tỉnh thành.'],
            ['Tôi có thể theo dõi đơn hàng không?', 'Có! Sau khi đơn hàng được giao cho đơn vị vận chuyển, bạn sẽ nhận được mã theo dõi qua email/SMS. Bạn cũng có thể xem trạng thái tại trang "Đơn hàng của tôi".'],
        ],
    ];
    @endphp

    @foreach($faqs as $category => $questions)
    <div style="margin-bottom:32px;">
        <h2 style="font-size:16px;font-weight:700;color:#6345B5;text-transform:uppercase;letter-spacing:1px;margin-bottom:16px;padding-bottom:10px;border-bottom:2px solid rgba(138,111,214,.3);">
            {{ $category }}
        </h2>
        <div style="background:#fff;border-radius:14px;box-shadow:0 2px 12px rgba(123,94,201,.06);overflow:hidden;border:1px solid #EAE1F8;">
            @foreach($questions as $i => $faq)
            <div class="faq-item" style="{{ !$loop->last ? 'border-bottom:1px solid #F3EDFB;' : '' }}">
                <button class="faq-btn" onclick="toggleFaq(this)"
                    style="width:100%;padding:20px 24px;background:none;border:none;text-align:left;display:flex;align-items:center;justify-content:space-between;cursor:pointer;font-size:14.5px;font-weight:600;color:#2D243D;">
                    <span>{{ $faq[0] }}</span>
                    <i class="fas fa-plus faq-icon" style="color:#8A6FD6;font-size:14px;flex-shrink:0;margin-left:16px;transition:.2s;"></i>
                </button>
                <div class="faq-answer" style="display:none;padding:0 24px 20px;font-size:14px;color:#666;line-height:1.8;">
                    {{ $faq[1] }}
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach

    {{-- Still have questions --}}
    <div style="text-align:center;background:#FAF7FE;border-radius:14px;padding:32px;border:1px solid #EAE1F8;">
        <h4 style="font-weight:700;margin-bottom:12px;">Vẫn còn câu hỏi?</h4>
        <p style="color:#888;font-size:14px;margin-bottom:20px;">Đội ngũ chúng tôi luôn sẵn sàng hỗ trợ bạn</p>
        <a href="{{ route('pages.contact') }}"
           style="display:inline-block;background:linear-gradient(135deg,#8A6FD6,#6345B5);color:#fff;padding:12px 28px;border-radius:12px;font-weight:700;text-decoration:none;box-shadow:0 4px 20px rgba(138,111,214,.35);">
            <i class="fas fa-headset me-2"></i>Liên Hệ Hỗ Trợ
        </a>
    </div>
</div>
@endsection

@section('scripts')
<script>
function toggleFaq(btn) {
    const answer = btn.nextElementSibling;
    const icon = btn.querySelector('.faq-icon');
    const isOpen = answer.style.display !== 'none';
    answer.style.display = isOpen ? 'none' : 'block';
    icon.style.transform = isOpen ? 'rotate(0)' : 'rotate(45deg)';
}
</script>
@endsection
