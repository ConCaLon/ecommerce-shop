@extends('layouts.app')

@section('title', 'Đặt Lại Mật Khẩu')

@section('content')
<div class="container" style="max-width:480px;margin:80px auto;padding:0 16px;">
    <div class="auth-card">
        <div class="auth-header text-center mb-5">
            <div class="auth-icon mb-3">
                <i class="fas fa-lock-open" style="font-size:2rem;color:var(--gold);"></i>
            </div>
            <h2 class="auth-title">Đặt Lại Mật Khẩu</h2>
            <p class="auth-subtitle">Nhập mật khẩu mới cho tài khoản của bạn</p>
        </div>

        <form method="POST" action="{{ route('password.update') }}" class="auth-form">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="mb-4">
                <label class="form-label">Mật khẩu mới <span class="text-danger">*</span></label>
                <div class="input-group-luxbag">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" name="password" id="password"
                           class="form-control @error('password') is-invalid @enderror"
                           placeholder="Ít nhất 8 ký tự, có chữ và số" required autocomplete="new-password">
                    <button type="button" class="toggle-password" onclick="togglePwd('password', this)" tabindex="-1">
                        <i class="fas fa-eye"></i>
                    </button>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-5">
                <label class="form-label">Xác nhận mật khẩu mới <span class="text-danger">*</span></label>
                <div class="input-group-luxbag">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" name="password_confirmation" id="password_confirmation"
                           class="form-control"
                           placeholder="Nhập lại mật khẩu mới" required autocomplete="new-password">
                    <button type="button" class="toggle-password" onclick="togglePwd('password_confirmation', this)" tabindex="-1">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            {{-- Password strength meter --}}
            <div class="mb-4">
                <div style="height:4px;border-radius:4px;background:#eee;overflow:hidden;">
                    <div id="strengthBar" style="height:100%;width:0;transition:all .3s;border-radius:4px;"></div>
                </div>
                <small id="strengthText" style="color:var(--text-muted);font-size:12px;"></small>
            </div>

            <button type="submit" class="btn-luxbag-primary w-100 py-3">
                <i class="fas fa-check-circle me-2"></i>
                Đặt Lại Mật Khẩu
            </button>
        </form>

        <div class="text-center mt-4">
            <a href="{{ route('login') }}" class="text-gold" style="font-size:14px;">
                <i class="fas fa-arrow-left me-1"></i>Quay lại đăng nhập
            </a>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function togglePwd(id, btn) {
    const input = document.getElementById(id);
    const isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';
    btn.querySelector('i').className = isHidden ? 'fas fa-eye-slash' : 'fas fa-eye';
}

document.getElementById('password').addEventListener('input', function() {
    const val = this.value;
    let strength = 0;
    let color = '#E74C3C';
    let text = '';

    if (val.length >= 8) strength++;
    if (/[A-Z]/.test(val)) strength++;
    if (/[0-9]/.test(val)) strength++;
    if (/[^A-Za-z0-9]/.test(val)) strength++;

    const bar = document.getElementById('strengthBar');
    const label = document.getElementById('strengthText');

    if (strength === 0) { bar.style.width = '0'; label.textContent = ''; return; }
    if (strength === 1) { color = '#E74C3C'; text = 'Yếu'; }
    else if (strength === 2) { color = '#F39C12'; text = 'Trung bình'; }
    else if (strength === 3) { color = '#27AE60'; text = 'Mạnh'; }
    else { color = '#1E8449'; text = 'Rất mạnh'; }

    bar.style.width = (strength * 25) + '%';
    bar.style.background = color;
    label.textContent = 'Độ mạnh: ' + text;
    label.style.color = color;
});
</script>
@endsection
