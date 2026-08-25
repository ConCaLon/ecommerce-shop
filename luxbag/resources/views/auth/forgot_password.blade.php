@extends('layouts.app')
@section('title', 'Quên Mật Khẩu')

@section('content')
<div class="auth-page">
    <div class="auth-card">
        <a href="{{ route('home') }}" class="auth-logo">Bloom & Bold</a>
        <p class="auth-subtitle">Nhập email để nhận link đặt lại mật khẩu.</p>

        @if(session('success'))
        <div class="alert" style="background:rgba(39,174,96,.1);border:1px solid rgba(39,174,96,.3);border-radius:8px;color:#1E8449;font-size:13px;padding:12px 16px;margin-bottom:20px;">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        </div>
        @endif

        <form action="{{ route('password.email') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="form-label fw-600" style="font-size:13px;">Email đăng ký</label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email') }}" placeholder="example@email.com" required autofocus>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <button type="submit" class="btn-gold w-100 justify-content-center py-3">
                <i class="fas fa-paper-plane"></i> Gửi Link Đặt Lại
            </button>
        </form>

        <p class="text-center mt-4" style="font-size:13px;">
            <a href="{{ route('login') }}" style="color:var(--gold);">
                <i class="fas fa-arrow-left me-1"></i>Quay lại đăng nhập
            </a>
        </p>
    </div>
</div>
@endsection
