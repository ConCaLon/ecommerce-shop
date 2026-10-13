@extends('layouts.app')
@section('title', 'Đăng Ký Tài Khoản')

@section('content')
<div class="auth-page">
    <div class="auth-card">
        <a href="{{ route('home') }}" class="auth-logo">Bloom & Bold</a>
        <p class="auth-subtitle">Tạo tài khoản để trải nghiệm mua sắm đỉnh cao.</p>

        <form action="{{ route('register') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-600" style="font-size:13px;">Họ và tên <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name') }}" placeholder="Nguyễn Văn A" required autofocus>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-600" style="font-size:13px;">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email') }}" placeholder="example@email.com" required>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="form-text">Chúng tôi sẽ gửi liên kết xác thực đến email này để kích hoạt tài khoản.</div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-600" style="font-size:13px;">Số điện thoại</label>
                <input type="tel" name="phone" class="form-control"
                       value="{{ old('phone') }}" placeholder="0901234567">
            </div>

            <div class="mb-3">
                <label class="form-label fw-600" style="font-size:13px;">Mật khẩu <span class="text-danger">*</span></label>
                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                       placeholder="Ít nhất 8 ký tự, có chữ và số" required>
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4">
                <label class="form-label fw-600" style="font-size:13px;">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                <input type="password" name="password_confirmation" class="form-control" placeholder="Nhập lại mật khẩu" required>
            </div>

            <div class="mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="agree" required>
                    <label class="form-check-label" for="agree" style="font-size:12px;color:var(--gray-600);">
                        Tôi đồng ý với
                        <a href="{{ route('pages.policy') }}" style="color:var(--gold);">Điều khoản & Điều kiện</a>
                        của Bloom & Bold
                    </label>
                </div>
            </div>

            <button type="submit" class="btn-gold w-100 justify-content-center py-3" style="font-size:15px;">
                <i class="fas fa-user-plus"></i> Tạo Tài Khoản
            </button>
        </form>

        <p class="text-center mt-4" style="font-size:13px;color:var(--gray-600);">
            Đã có tài khoản?
            <a href="{{ route('login') }}" style="color:var(--gold);font-weight:600;">Đăng nhập</a>
        </p>
    </div>
</div>
@endsection
