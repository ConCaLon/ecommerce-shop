@extends('layouts.app')
@section('title', 'Xác Thực Email')

@section('content')
<div class="auth-page">
    <div class="auth-card">
        <a href="{{ route('home') }}" class="auth-logo">Bloom & Bold</a>
        <div class="text-center mb-3" style="font-size:36px;color:var(--gold);" aria-hidden="true">
            <i class="fas fa-envelope-open-text"></i>
        </div>
        <h1 class="text-center mb-3" style="font-size:24px;">Kiểm tra email của bạn</h1>
        <p class="auth-subtitle">
            Tài khoản của bạn đang chờ xác thực. Hãy mở email gửi đến
            <strong class="d-block mt-2" style="overflow-wrap:anywhere;">{{ $user->email }}</strong>
            và nhấn <strong>Xác thực tài khoản</strong> để kích hoạt tài khoản mua hàng.
        </p>

        @foreach(['success' => 'success', 'error' => 'danger', 'warning' => 'warning'] as $type => $class)
            @if(session($type))
            <div class="alert alert-{{ $class }}" role="alert">{{ session($type) }}</div>
            @endif
        @endforeach

        <p style="font-size:13px;color:var(--gray-600);">
            Liên kết có hiệu lực trong {{ $expiresInMinutes }} phút. Nếu chưa thấy email,
            hãy kiểm tra thư mục Spam hoặc nhấn nút gửi lại bên dưới.
            Khi mở liên kết trên trình duyệt khác, hãy đăng nhập bằng tài khoản vừa đăng ký.
        </p>

        <form action="{{ route('verification.send') }}" method="POST" class="mt-4">
            @csrf
            <button type="submit" id="resendVerification" class="btn-gold w-100 justify-content-center py-3"
                    @disabled($retryAfter > 0)>
                <i class="fas fa-paper-plane" aria-hidden="true"></i>
                <span id="resendVerificationLabel">{{ $retryAfter > 0 ? 'Gửi lại sau '.$retryAfter.' giây' : 'Gửi lại email xác thực' }}</span>
            </button>
            <p class="text-center mt-2" style="font-size:12px;color:var(--gray-600);" aria-live="polite">
                Mỗi lần gửi lại cách nhau ít nhất một phút.
            </p>
        </form>

        <a href="{{ route('home') }}" class="d-block text-center mt-3" style="color:var(--gold);">Tiếp tục xem sản phẩm</a>
        <form action="{{ route('logout') }}" method="POST" class="text-center mt-3">
            @csrf
            <button type="submit" class="btn btn-link" style="font-size:13px;color:var(--gray-600);">Đăng xuất / Dùng tài khoản khác</button>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
(() => {
    let remaining = {{ (int) $retryAfter }};
    const button = document.getElementById('resendVerification');
    const label = document.getElementById('resendVerificationLabel');
    if (remaining <= 0) return;
    const countdown = setInterval(() => {
        remaining -= 1;
        label.textContent = remaining > 0 ? `Gửi lại sau ${remaining} giây` : 'Gửi lại email xác thực';
        if (remaining <= 0) {
            button.disabled = false;
            clearInterval(countdown);
        }
    }, 1000);
})();
</script>
@endsection
