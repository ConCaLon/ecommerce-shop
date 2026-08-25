@extends('layouts.admin')

@section('title', 'Thêm Nhân Viên Mới')
@section('page-title', 'Thêm Nhân Viên')
@section('breadcrumb')
    <a href="{{ route('admin.staff.index') }}">Nhân Viên</a>
    <span class="separator">/</span> Thêm mới
@endsection

@section('content')

<div class="row justify-content-center">
    <div class="col-xl-7 col-lg-9">

        {{-- ─── Header Card ─── --}}
        <div class="admin-card mb-4" style="background:linear-gradient(135deg,#1e293b,#0f172a);border:1px solid rgba(212,175,55,.2);">
            <div class="d-flex align-items-center gap-3">
                <div style="width:56px;height:56px;background:linear-gradient(135deg,var(--gold),#b8860b);border-radius:14px;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-user-plus" style="color:#fff;font-size:22px;"></i>
                </div>
                <div>
                    <h5 class="fw-700 mb-1" style="color:#fff;">Tạo Tài Khoản Nhân Viên</h5>
                    <p style="color:rgba(255,255,255,.5);font-size:13px;margin:0;">Thêm thành viên mới vào đội ngũ quản trị</p>
                </div>
            </div>
        </div>

        {{-- ─── Form ─── --}}
        <form action="{{ route('admin.staff.store') }}" method="POST">
            @csrf

            {{-- Thông tin cơ bản --}}
            <div class="admin-card mb-4">
                <h6 class="fw-700 mb-4" style="display:flex;align-items:center;gap:8px;">
                    <i class="fas fa-id-card" style="color:var(--gold);"></i> Thông Tin Cơ Bản
                </h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-600" style="font-size:13px;">Họ và tên <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" placeholder="Nguyễn Văn A"
                               style="border-radius:8px;font-size:13.5px;padding:10px 14px;">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-600" style="font-size:13px;">Số điện thoại</label>
                        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                               value="{{ old('phone') }}" placeholder="0901 234 567"
                               style="border-radius:8px;font-size:13.5px;padding:10px 14px;">
                        @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-600" style="font-size:13px;">Email đăng nhập <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}" placeholder="nhanvien@luxbag.vn"
                               style="border-radius:8px;font-size:13.5px;padding:10px 14px;">
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            {{-- Mật khẩu --}}
            <div class="admin-card mb-4">
                <h6 class="fw-700 mb-4" style="display:flex;align-items:center;gap:8px;">
                    <i class="fas fa-lock" style="color:var(--gold);"></i> Mật Khẩu
                </h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-600" style="font-size:13px;">Mật khẩu <span class="text-danger">*</span></label>
                        <div class="position-relative">
                            <input type="password" name="password" id="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   placeholder="Tối thiểu 8 ký tự, có chữ và số"
                                   style="border-radius:8px;font-size:13.5px;padding:10px 44px 10px 14px;">
                            <button type="button" onclick="togglePass('password')"
                                    style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-muted);cursor:pointer;">
                                <i class="fas fa-eye" id="password-eye"></i>
                            </button>
                        </div>
                        @error('password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-600" style="font-size:13px;">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                        <div class="position-relative">
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                   class="form-control"
                                   placeholder="Nhập lại mật khẩu"
                                   style="border-radius:8px;font-size:13.5px;padding:10px 44px 10px 14px;">
                            <button type="button" onclick="togglePass('password_confirmation')"
                                    style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-muted);cursor:pointer;">
                                <i class="fas fa-eye" id="password_confirmation-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Vai trò --}}
            <div class="admin-card mb-4">
                <h6 class="fw-700 mb-4" style="display:flex;align-items:center;gap:8px;">
                    <i class="fas fa-shield-alt" style="color:var(--gold);"></i> Vai Trò & Quyền Hạn
                </h6>
                <div class="row g-3">
                    @foreach($roles as $role)
                    <div class="col-md-6">
                        <label class="d-block" style="cursor:pointer;">
                            <input type="radio" name="role" value="{{ $role->name }}"
                                   {{ old('role') == $role->name || ($loop->first && !old('role')) ? 'checked' : '' }}
                                   style="display:none;" class="role-radio">
                            <div class="role-card {{ old('role', $roles->first()->name) == $role->name ? 'selected' : '' }}"
                                 style="padding:16px;border:2px solid var(--border);border-radius:12px;transition:all .2s;position:relative;">
                                @if($role->name === 'manager')
                                <div style="font-weight:700;color:#0ea5e9;margin-bottom:6px;">
                                    <i class="fas fa-user-tie me-2"></i>Quản lý (Manager)
                                </div>
                                <p style="font-size:12px;color:var(--text-muted);margin:0;line-height:1.6;">
                                    Toàn quyền hệ thống: quản lý sản phẩm, đơn hàng, khách hàng, mã giảm giá, nhân viên và cài đặt.
                                </p>
                                @else
                                <div style="font-weight:700;color:#10b981;margin-bottom:6px;">
                                    <i class="fas fa-user me-2"></i>Nhân viên (Staff)
                                </div>
                                <p style="font-size:12px;color:var(--text-muted);margin:0;line-height:1.6;">
                                    Quyền hạn giới hạn: sản phẩm, danh mục, thương hiệu, đơn hàng và đánh giá.
                                </p>
                                @endif
                                <div class="role-check" style="position:absolute;top:12px;right:12px;width:20px;height:20px;border-radius:50%;border:2px solid var(--border);display:flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-check" style="font-size:10px;color:#fff;display:none;"></i>
                                </div>
                            </div>
                        </label>
                    </div>
                    @endforeach
                </div>
                @error('role') <div class="text-danger mt-2" style="font-size:13px;">{{ $message }}</div> @enderror
            </div>

            {{-- Actions --}}
            <div class="d-flex gap-3 justify-content-end">
                <a href="{{ route('admin.staff.index') }}" class="btn-admin-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Quay lại
                </a>
                <button type="submit" class="btn-admin-primary">
                    <i class="fas fa-user-plus me-1"></i> Tạo Tài Khoản
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<style>
.role-radio:checked + .role-card,
.role-card.selected {
    border-color: var(--gold) !important;
    background: rgba(212,175,55,.05);
}
.role-radio:checked + .role-card .role-check,
.role-card.selected .role-check {
    background: var(--gold);
    border-color: var(--gold);
}
.role-radio:checked + .role-card .role-check i,
.role-card.selected .role-check i {
    display: block !important;
}
</style>
<script>
function togglePass(id) {
    const input = document.getElementById(id);
    const eye = document.getElementById(id + '-eye');
    if (input.type === 'password') {
        input.type = 'text';
        eye.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        eye.className = 'fas fa-eye';
    }
}

// Role card selection
document.querySelectorAll('.role-radio').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.role-card').forEach(c => c.classList.remove('selected'));
        this.nextElementSibling.classList.add('selected');
    });
});
</script>
@endsection
