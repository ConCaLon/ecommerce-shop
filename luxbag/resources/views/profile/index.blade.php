@extends('layouts.app')

@section('title', 'Tài Khoản Của Tôi')
@section('meta_description', 'Quản lý thông tin tài khoản Bloom & Bold của bạn.')

@section('head')
<style>
.profile-nav .nav-link { border-radius:10px; color:#544473; font-size:14px; font-weight:600; padding:10px 16px; display:flex; align-items:center; gap:10px; margin-bottom:4px; text-decoration:none; transition:.2s; }
.profile-nav .nav-link:hover, .profile-nav .nav-link.active { background:rgba(138,111,214,.15); color:#8A6FD6; }
.profile-section { background:#fff; border-radius:16px; box-shadow:0 2px 20px rgba(123,94,201,.06); padding:28px; margin-bottom:24px; border:1px solid #EAE1F8; }
.profile-section h5 { font-weight:700; font-size:16px; margin-bottom:20px; padding-bottom:14px; border-bottom:1px solid #F3EDFB; }
.form-field label { font-size:13px; font-weight:600; color:#444; margin-bottom:6px; display:block; }
.form-field input, .form-field select { border-radius:10px; border:1.5px solid #E2D5F7; font-size:14px; padding:11px 14px; width:100%; transition:.2s; outline:none; }
.form-field input:focus, .form-field select:focus { border-color:#8A6FD6; box-shadow:0 0 0 3px rgba(138,111,214,.2); }
.address-card { border:1.5px solid #EAE1F8; border-radius:12px; padding:18px; margin-bottom:12px; position:relative; transition:.2s; }
.address-card.default { border-color:#8A6FD6; background:rgba(138,111,214,.06); }
</style>
@endsection

@section('content')
<div class="container py-5">
    <h1 class="h3 fw-700 mb-4">Tài Khoản Của Tôi</h1>

    <div class="row g-4">
        {{-- ─── Sidebar ─── --}}
        <div class="col-lg-3">
            {{-- Profile Card --}}
            <div style="background:#fff;border-radius:16px;box-shadow:0 2px 20px rgba(123,94,201,.06);padding:24px;text-align:center;margin-bottom:16px;border:1px solid #EAE1F8;">
                {{-- Avatar --}}
                <div style="position:relative;display:inline-block;margin-bottom:16px;">
                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}"
                         id="avatarPreview"
                         style="width:80px;height:80px;border-radius:50%;object-fit:cover;border:3px solid #8A6FD6;">
                    <label for="avatarUpload"
                           style="position:absolute;bottom:0;right:0;width:26px;height:26px;border-radius:50%;background:linear-gradient(135deg,#8A6FD6,#6345B5);display:flex;align-items:center;justify-content:center;cursor:pointer;border:2px solid #fff;">
                        <i class="fas fa-camera" style="font-size:11px;color:#fff;"></i>
                    </label>
                    <form id="avatarForm" method="POST" action="{{ route('profile.avatar') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="file" id="avatarUpload" name="avatar" accept="image/*" style="display:none;" onchange="uploadAvatar(this)">
                    </form>
                </div>
                <div style="font-weight:700;font-size:16px;">{{ $user->name }}</div>
                <div style="font-size:13px;color:#888;margin-top:2px;">{{ $user->email }}</div>
                <div style="font-size:12px;color:#aaa;margin-top:4px;">Thành viên từ {{ $user->created_at->format('d/m/Y') }}</div>

                {{-- Stats preview --}}
                <div class="row g-2 mt-3 pt-3" style="border-top:1px solid #F3EDFB;">
                    <div class="col-6">
                        <div style="font-size:20px;font-weight:800;color:#8A6FD6;">{{ $orderStats['total'] }}</div>
                        <div style="font-size:11px;color:#888;">Đơn hàng</div>
                    </div>
                    <div class="col-6">
                        <div style="font-size:20px;font-weight:800;color:#27AE60;">{{ $orderStats['delivered'] }}</div>
                        <div style="font-size:11px;color:#888;">Hoàn thành</div>
                    </div>
                </div>
            </div>

            {{-- Navigation --}}
            <div style="background:#fff;border-radius:16px;box-shadow:0 2px 20px rgba(123,94,201,.06);padding:12px;border:1px solid #EAE1F8;" class="profile-nav">
                <a href="#info" class="nav-link active" onclick="showTab('info', this)">
                    <i class="fas fa-user-circle"></i>Thông tin cá nhân
                </a>
                <a href="#addresses" class="nav-link" onclick="showTab('addresses', this)">
                    <i class="fas fa-map-marker-alt"></i>Sổ địa chỉ
                </a>
                <a href="#password" class="nav-link" onclick="showTab('password', this)">
                    <i class="fas fa-lock"></i>Đổi mật khẩu
                </a>
                <a href="{{ route('orders.history') }}" class="nav-link">
                    <i class="fas fa-box"></i>Lịch sử đơn hàng
                </a>
                <a href="{{ route('wishlist.index') }}" class="nav-link">
                    <i class="fas fa-heart"></i>Sản phẩm yêu thích
                </a>
                <hr style="margin:8px 0;border-color:#F3EDFB;">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="nav-link text-danger w-100 border-0 bg-transparent" style="cursor:pointer;">
                        <i class="fas fa-sign-out-alt"></i>Đăng xuất
                    </button>
                </form>
            </div>
        </div>

        {{-- ─── Main Content ─── --}}
        <div class="col-lg-9">
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>Có lỗi xảy ra, vui lòng kiểm tra lại.
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            {{-- ─── Info Tab ─── --}}
            <div id="tab-info">
                <div class="profile-section">
                    <h5><i class="fas fa-user me-2" style="color:#8A6FD6;"></i>Thông Tin Cá Nhân</h5>
                    <form method="POST" action="{{ route('profile.update') }}">
                        @csrf @method('PUT')
                        <div class="row g-3">
                            <div class="col-md-6 form-field">
                                <label>Họ và tên <span class="text-danger">*</span></label>
                                <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
                            </div>
                            <div class="col-md-6 form-field">
                                <label>Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
                            </div>
                            <div class="col-md-6 form-field">
                                <label>Số điện thoại</label>
                                <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="0912 345 678">
                            </div>
                            <div class="col-md-6 form-field">
                                <label>Ngày sinh</label>
                                <input type="date" name="birthday" value="{{ old('birthday', $user->birthday?->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-6 form-field">
                                <label>Giới tính</label>
                                <select name="gender">
                                    <option value="">— Chọn giới tính —</option>
                                    <option value="male" {{ old('gender', $user->gender) == 'male' ? 'selected' : '' }}>Nam</option>
                                    <option value="female" {{ old('gender', $user->gender) == 'female' ? 'selected' : '' }}>Nữ</option>
                                    <option value="other" {{ old('gender', $user->gender) == 'other' ? 'selected' : '' }}>Khác</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="submit"
                                style="background:linear-gradient(135deg,#8A6FD6,#6345B5);color:#fff;border:none;padding:12px 28px;border-radius:12px;font-weight:700;font-size:14px;cursor:pointer;box-shadow:0 4px 18px rgba(138,111,214,.35);">
                                <i class="fas fa-save me-2"></i>Lưu Thông Tin
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ─── Addresses Tab ─── --}}
            <div id="tab-addresses" style="display:none;">
                <div class="profile-section">
                    <h5><i class="fas fa-map-marker-alt me-2" style="color:#8A6FD6;"></i>Địa Chỉ Của Tôi</h5>

                    {{-- Add new address form --}}
                    <div style="background:#FAF7FE;border-radius:12px;padding:20px;margin-bottom:24px;border:1px solid #EAE1F8;">
                        <h6 style="font-weight:700;margin-bottom:16px;">Thêm Địa Chỉ Mới</h6>
                        <form method="POST" action="{{ route('profile.addresses.store') }}">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6 form-field">
                                    <label>Họ và tên <span class="text-danger">*</span></label>
                                    <input type="text" name="full_name" required value="{{ $user->name }}">
                                </div>
                                <div class="col-md-6 form-field">
                                    <label>Số điện thoại <span class="text-danger">*</span></label>
                                    <input type="text" name="phone" required value="{{ $user->phone }}">
                                </div>
                                <div class="col-md-4 form-field">
                                    <label>Tỉnh/Thành <span class="text-danger">*</span></label>
                                    <input type="text" name="province" required placeholder="TP. Hồ Chí Minh">
                                </div>
                                <div class="col-md-4 form-field">
                                    <label>Quận/Huyện <span class="text-danger">*</span></label>
                                    <input type="text" name="district" required placeholder="Quận 1">
                                </div>
                                <div class="col-md-4 form-field">
                                    <label>Phường/Xã <span class="text-danger">*</span></label>
                                    <input type="text" name="ward" required placeholder="Phường Bến Nghé">
                                </div>
                                <div class="col-12 form-field">
                                    <label>Địa chỉ cụ thể <span class="text-danger">*</span></label>
                                    <input type="text" name="address_detail" required placeholder="Số nhà, tên đường...">
                                </div>
                                <div class="col-12">
                                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px;font-weight:600;">
                                        <input type="checkbox" name="is_default" value="1" style="width:16px;height:16px;">
                                        Đặt làm địa chỉ mặc định
                                    </label>
                                </div>
                            </div>
                            <button type="submit"
                                style="margin-top:16px;background:linear-gradient(135deg,#8A6FD6,#6345B5);color:#fff;border:none;padding:10px 24px;border-radius:10px;font-weight:600;font-size:13px;cursor:pointer;box-shadow:0 4px 15px rgba(138,111,214,.3);">
                                <i class="fas fa-plus me-1"></i>Thêm Địa Chỉ
                            </button>
                        </form>
                    </div>

                    {{-- Existing addresses --}}
                    @php $addresses = auth()->user()->addresses()->orderByDesc('is_default')->get(); @endphp
                    @forelse($addresses as $addr)
                    <div class="address-card {{ $addr->is_default ? 'default' : '' }}">
                        @if($addr->is_default)
                        <span style="position:absolute;top:12px;right:12px;font-size:10px;background:rgba(138,111,214,.15);color:#8A6FD6;padding:2px 8px;border-radius:4px;font-weight:700;">✓ Mặc định</span>
                        @endif
                        <div style="font-weight:700;font-size:14px;">{{ $addr->full_name }} <span style="font-weight:500;color:#888;">{{ $addr->phone }}</span></div>
                        <div style="font-size:13px;color:#666;margin-top:4px;">{{ $addr->address_detail }}, {{ $addr->ward }}, {{ $addr->district }}, {{ $addr->province }}</div>
                        <div class="d-flex gap-2 mt-3">
                            @if(!$addr->is_default)
                            <form method="POST" action="{{ route('profile.addresses.default', $addr->id) }}">
                                @csrf
                                <button type="submit" style="font-size:12px;padding:6px 14px;background:#fff;border:1px solid #8A6FD6;color:#8A6FD6;border-radius:8px;font-weight:600;cursor:pointer;">
                                    Đặt làm mặc định
                                </button>
                            </form>
                            @endif
                            <form method="POST" action="{{ route('profile.addresses.delete', $addr->id) }}">
                                @csrf @method('DELETE')
                                <button type="submit" style="font-size:12px;padding:6px 14px;background:#fff;border:1px solid #e8e8e8;color:#e74c3c;border-radius:8px;font-weight:600;cursor:pointer;"
                                    onclick="return confirm('Xóa địa chỉ này?')">
                                    <i class="fas fa-trash me-1"></i>Xóa
                                </button>
                            </form>
                        </div>
                    </div>
                    @empty
                    <div style="text-align:center;padding:40px;color:#aaa;">
                        <i class="fas fa-map-marker-alt" style="font-size:36px;display:block;margin-bottom:12px;opacity:.3;"></i>
                        Bạn chưa có địa chỉ nào
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- ─── Password Tab ─── --}}
            <div id="tab-password" style="display:none;">
                <div class="profile-section">
                    <h5><i class="fas fa-lock me-2" style="color:#8A6FD6;"></i>Đổi Mật Khẩu</h5>
                    <form method="POST" action="{{ route('profile.password') }}" style="max-width:480px;">
                        @csrf @method('PUT')
                        <div class="row g-3">
                            <div class="col-12 form-field">
                                <label>Mật khẩu hiện tại <span class="text-danger">*</span></label>
                                <div style="position:relative;">
                                    <input type="password" name="current_password" required id="curPwd" style="padding-right:44px;">
                                    <button type="button" onclick="togglePwd('curPwd', this)"
                                        style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:#888;cursor:pointer;font-size:14px;">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-12 form-field">
                                <label>Mật khẩu mới <span class="text-danger">*</span></label>
                                <div style="position:relative;">
                                    <input type="password" name="password" required id="newPwd" placeholder="Tối thiểu 8 ký tự" style="padding-right:44px;">
                                    <button type="button" onclick="togglePwd('newPwd', this)"
                                        style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:#888;cursor:pointer;font-size:14px;">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-12 form-field">
                                <label>Xác nhận mật khẩu mới <span class="text-danger">*</span></label>
                                <input type="password" name="password_confirmation" required id="confirmPwd" placeholder="Nhập lại mật khẩu mới">
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="submit"
                                style="background:linear-gradient(135deg,#8A6FD6,#6345B5);color:#fff;border:none;padding:12px 28px;border-radius:12px;font-weight:700;font-size:14px;cursor:pointer;box-shadow:0 4px 18px rgba(138,111,214,.35);">
                                <i class="fas fa-key me-2"></i>Đổi Mật Khẩu
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function showTab(tab, el) {
    event.preventDefault();
    document.querySelectorAll('[id^="tab-"]').forEach(t => t.style.display = 'none');
    document.getElementById(`tab-${tab}`).style.display = 'block';
    document.querySelectorAll('.profile-nav .nav-link').forEach(l => l.classList.remove('active'));
    el.classList.add('active');
}

function togglePwd(id, btn) {
    const input = document.getElementById(id);
    if (input.type === 'password') {
        input.type = 'text';
        btn.innerHTML = '<i class="fas fa-eye-slash"></i>';
    } else {
        input.type = 'password';
        btn.innerHTML = '<i class="fas fa-eye"></i>';
    }
}

function uploadAvatar(input) {
    const file = input.files[0];
    if (!file) return;
    // Preview
    const reader = new FileReader();
    reader.onload = e => document.getElementById('avatarPreview').src = e.target.result;
    reader.readAsDataURL(file);
    // Submit form
    document.getElementById('avatarForm').submit();
}

// Open tab based on URL hash
const hash = location.hash.replace('#', '');
if (hash && document.getElementById(`tab-${hash}`)) {
    document.querySelector(`[href="#${hash}"]`)?.click();
}
</script>
@endsection
