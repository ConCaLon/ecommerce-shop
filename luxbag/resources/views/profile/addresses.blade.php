@extends('layouts.app')

@section('title', 'Địa Chỉ Của Tôi')

@section('content')
<div class="container py-5" style="max-width:800px;">
    <nav style="font-size:13px;color:#888;margin-bottom:24px;">
        <a href="{{ route('profile.index') }}" style="color:#888;text-decoration:none;">Tài khoản</a>
        <i class="fas fa-chevron-right mx-2" style="font-size:10px;"></i>
        <strong style="color:#1a1d24;">Địa chỉ</strong>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 fw-700 mb-0">📍 Địa Chỉ Của Tôi</h1>
        <a href="{{ route('profile.index') }}" style="color:#888;font-size:13px;text-decoration:none;">
            <i class="fas fa-arrow-left me-1"></i>Quay lại tài khoản
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success mb-4 rounded-3" style="border-left:4px solid #27AE60;">
        <i class="fas fa-check-circle me-2" style="color:#27AE60;"></i>{{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger mb-4 rounded-3">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
    </div>
    @endif

    {{-- Add New Address --}}
    <div style="background:#fff;border-radius:16px;box-shadow:0 2px 20px rgba(123,94,201,.06);border:1px solid #EAE1F8;padding:28px;margin-bottom:24px;">
        <h5 style="font-weight:700;font-size:16px;margin-bottom:20px;padding-bottom:14px;border-bottom:1px solid #F3EDFB;">
            <i class="fas fa-plus-circle me-2" style="color:#8A6FD6;"></i>Thêm Địa Chỉ Mới
        </h5>
        <form method="POST" action="{{ route('profile.addresses.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label style="font-size:13px;font-weight:600;display:block;margin-bottom:6px;">Họ và tên <span class="text-danger">*</span></label>
                    <input type="text" name="full_name" class="form-control" required value="{{ auth()->user()->name }}"
                           style="border-radius:10px;border:1.5px solid #E2D5F7;font-size:14px;padding:11px 14px;">
                </div>
                <div class="col-md-6">
                    <label style="font-size:13px;font-weight:600;display:block;margin-bottom:6px;">Số điện thoại <span class="text-danger">*</span></label>
                    <input type="text" name="phone" class="form-control" required value="{{ auth()->user()->phone }}"
                           style="border-radius:10px;border:1.5px solid #E2D5F7;font-size:14px;padding:11px 14px;">
                </div>
                <div class="col-md-4">
                    <label style="font-size:13px;font-weight:600;display:block;margin-bottom:6px;">Tỉnh/Thành phố <span class="text-danger">*</span></label>
                    <input type="text" name="province" class="form-control" required placeholder="VD: TP. Hồ Chí Minh"
                           style="border-radius:10px;border:1.5px solid #E2D5F7;font-size:14px;padding:11px 14px;">
                </div>
                <div class="col-md-4">
                    <label style="font-size:13px;font-weight:600;display:block;margin-bottom:6px;">Quận/Huyện <span class="text-danger">*</span></label>
                    <input type="text" name="district" class="form-control" required placeholder="VD: Quận 1"
                           style="border-radius:10px;border:1.5px solid #E2D5F7;font-size:14px;padding:11px 14px;">
                </div>
                <div class="col-md-4">
                    <label style="font-size:13px;font-weight:600;display:block;margin-bottom:6px;">Phường/Xã <span class="text-danger">*</span></label>
                    <input type="text" name="ward" class="form-control" required placeholder="VD: Phường Bến Nghé"
                           style="border-radius:10px;border:1.5px solid #E2D5F7;font-size:14px;padding:11px 14px;">
                </div>
                <div class="col-12">
                    <label style="font-size:13px;font-weight:600;display:block;margin-bottom:6px;">Địa chỉ cụ thể <span class="text-danger">*</span></label>
                    <input type="text" name="address_detail" class="form-control" required placeholder="Số nhà, tên đường..."
                           style="border-radius:10px;border:1.5px solid #E2D5F7;font-size:14px;padding:11px 14px;">
                </div>
                <div class="col-12">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px;font-weight:600;">
                        <input type="checkbox" name="is_default" value="1" style="width:16px;height:16px;">
                        Đặt làm địa chỉ mặc định
                    </label>
                </div>
            </div>
            <button type="submit"
                style="margin-top:16px;background:linear-gradient(135deg,#8A6FD6,#6345B5);color:#fff;border:none;padding:12px 24px;border-radius:12px;font-weight:700;font-size:14px;cursor:pointer;box-shadow:0 4px 15px rgba(138,111,214,.35);">
                <i class="fas fa-plus me-2"></i>Thêm Địa Chỉ
            </button>
        </form>
    </div>

    {{-- Existing Addresses --}}
    <div style="background:#fff;border-radius:16px;box-shadow:0 2px 20px rgba(123,94,201,.06);border:1px solid #EAE1F8;padding:28px;">
        <h5 style="font-weight:700;font-size:16px;margin-bottom:20px;padding-bottom:14px;border-bottom:1px solid #F3EDFB;">
            Địa Chỉ Đã Lưu ({{ $addresses->count() }})
        </h5>

        @forelse($addresses as $addr)
        <div style="border:1.5px solid {{ $addr->is_default ? '#8A6FD6' : '#EAE1F8' }};border-radius:12px;padding:18px;margin-bottom:12px;{{ $addr->is_default ? 'background:rgba(138,111,214,.04);' : '' }}">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    @if($addr->is_default)
                    <span style="font-size:10px;background:rgba(138,111,214,.15);color:#8A6FD6;padding:2px 8px;border-radius:4px;font-weight:700;display:inline-block;margin-bottom:8px;">✓ Địa chỉ mặc định</span>
                    @endif
                    <div style="font-weight:700;font-size:14px;">{{ $addr->full_name }}</div>
                    <div style="font-size:13px;color:#888;margin-top:2px;">{{ $addr->phone }}</div>
                    <div style="font-size:13px;color:#666;margin-top:6px;line-height:1.7;">
                        {{ $addr->address_detail }}, {{ $addr->ward }}, {{ $addr->district }}, {{ $addr->province }}
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2 mt-3">
                @if(!$addr->is_default)
                <form method="POST" action="{{ route('profile.addresses.default', $addr->id) }}">
                    @csrf @method('PATCH')
                    <button type="submit" style="font-size:12px;padding:6px 14px;background:#fff;border:1px solid #8A6FD6;color:#8A6FD6;border-radius:8px;font-weight:600;cursor:pointer;">
                        Đặt làm mặc định
                    </button>
                </form>
                @endif
                <button type="button" onclick="toggleEdit({{ $addr->id }})"
                    style="font-size:12px;padding:6px 14px;background:#fff;border:1px solid #EAE1F8;color:#333;border-radius:8px;font-weight:600;cursor:pointer;">
                    <i class="fas fa-edit me-1"></i>Sửa
                </button>
                <form method="POST" action="{{ route('profile.addresses.delete', $addr->id) }}">
                    @csrf @method('DELETE')
                    <button type="submit" onclick="return confirm('Xóa địa chỉ này?')"
                        style="font-size:12px;padding:6px 14px;background:#fff;border:1px solid #EAE1F8;color:#e74c3c;border-radius:8px;font-weight:600;cursor:pointer;">
                        <i class="fas fa-trash me-1"></i>Xóa
                    </button>
                </form>
            </div>

            {{-- Edit Form (hidden) --}}
            <form method="POST" action="{{ route('profile.addresses.update', $addr->id) }}" id="edit-addr-{{ $addr->id }}" style="display:none;margin-top:16px;">
                @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <input type="text" name="full_name" class="form-control" value="{{ $addr->full_name }}" placeholder="Họ tên" style="border-radius:8px;border:1px solid #E2D5F7;font-size:13px;padding:9px 12px;">
                    </div>
                    <div class="col-md-6">
                        <input type="text" name="phone" class="form-control" value="{{ $addr->phone }}" placeholder="SĐT" style="border-radius:8px;border:1px solid #E2D5F7;font-size:13px;padding:9px 12px;">
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="province" class="form-control" value="{{ $addr->province }}" placeholder="Tỉnh/Thành" style="border-radius:8px;border:1px solid #E2D5F7;font-size:13px;padding:9px 12px;">
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="district" class="form-control" value="{{ $addr->district }}" placeholder="Quận/Huyện" style="border-radius:8px;border:1px solid #E2D5F7;font-size:13px;padding:9px 12px;">
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="ward" class="form-control" value="{{ $addr->ward }}" placeholder="Phường/Xã" style="border-radius:8px;border:1px solid #E2D5F7;font-size:13px;padding:9px 12px;">
                    </div>
                    <div class="col-12">
                        <input type="text" name="address_detail" class="form-control" value="{{ $addr->address_detail }}" placeholder="Địa chỉ cụ thể" style="border-radius:8px;border:1px solid #E2D5F7;font-size:13px;padding:9px 12px;">
                    </div>
                </div>
                <div class="d-flex gap-2 mt-3">
                    <button type="submit" style="background:linear-gradient(135deg,#8A6FD6,#6345B5);color:#fff;border:none;padding:8px 20px;border-radius:8px;font-weight:700;font-size:13px;cursor:pointer;box-shadow:0 4px 15px rgba(138,111,214,.35);">
                        <i class="fas fa-save me-1"></i>Lưu
                    </button>
                    <button type="button" onclick="toggleEdit({{ $addr->id }})" style="background:#f8f8f8;border:1px solid #EAE1F8;color:#666;padding:8px 16px;border-radius:8px;font-size:13px;cursor:pointer;">Hủy</button>
                </div>
            </form>
        </div>
        @empty
        <div style="text-align:center;padding:40px;color:#aaa;">
            <i class="fas fa-map-marker-alt" style="font-size:48px;display:block;margin-bottom:16px;opacity:.3;"></i>
            <p>Bạn chưa có địa chỉ nào. Thêm địa chỉ mới ở trên!</p>
        </div>
        @endforelse
    </div>
</div>
@endsection

@section('scripts')
<script>
function toggleEdit(id) {
    const form = document.getElementById(`edit-addr-${id}`);
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}
</script>
@endsection
