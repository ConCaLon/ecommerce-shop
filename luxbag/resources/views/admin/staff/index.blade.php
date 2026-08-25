@extends('layouts.admin')

@section('title', 'Quản Lý Nhân Viên')
@section('page-title', 'Quản Lý Nhân Viên')
@section('breadcrumb', 'Nhân Viên')

@section('content')

{{-- ─── Header ─── --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-700 mb-1">Đội Ngũ Nhân Viên</h5>
        <p style="color:var(--text-muted);font-size:13px;margin:0;">Quản lý tài khoản và phân quyền nhân viên nội bộ</p>
    </div>
    <a href="{{ route('admin.staff.create') }}" class="btn-admin-primary d-flex align-items-center gap-2">
        <i class="fas fa-user-plus"></i> Thêm Nhân Viên
    </a>
</div>

{{-- ─── Stats Cards ─── --}}
<div class="row g-3 mb-4">
    @php
        $total   = $staff->total();
        $managers = \App\Models\User::role('manager')->count();
        $staffs   = \App\Models\User::role('staff')->count();
        $admins   = \App\Models\User::role('admin')->count();
    @endphp
    <div class="col-sm-3">
        <div class="admin-card text-center py-3">
            <div style="font-size:28px;font-weight:700;color:var(--gold);">{{ $total }}</div>
            <div style="font-size:12px;color:var(--text-muted);margin-top:4px;">Tổng nhân viên</div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="admin-card text-center py-3">
            <div style="font-size:28px;font-weight:700;color:#7c3aed;">{{ $admins }}</div>
            <div style="font-size:12px;color:var(--text-muted);margin-top:4px;">Admin</div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="admin-card text-center py-3">
            <div style="font-size:28px;font-weight:700;color:#0ea5e9;">{{ $managers }}</div>
            <div style="font-size:12px;color:var(--text-muted);margin-top:4px;">Quản lý</div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="admin-card text-center py-3">
            <div style="font-size:28px;font-weight:700;color:#10b981;">{{ $staffs }}</div>
            <div style="font-size:12px;color:var(--text-muted);margin-top:4px;">Nhân viên</div>
        </div>
    </div>
</div>

{{-- ─── Filters ─── --}}
<div class="admin-card mb-4">
    <form method="GET" action="{{ route('admin.staff.index') }}" class="row g-3 align-items-end">
        <div class="col-md-4">
            <div class="admin-search">
                <i class="fas fa-search search-icon"></i>
                <input type="text" name="search" class="form-control" placeholder="Tìm tên, email, SĐT..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-md-3">
            <select name="role" class="form-select" style="border-radius:8px;font-size:13.5px;border:1px solid var(--border);padding:10px 14px;">
                <option value="">Tất cả vai trò</option>
                <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="manager" {{ request('role') == 'manager' ? 'selected' : '' }}>Quản lý</option>
                <option value="staff" {{ request('role') == 'staff' ? 'selected' : '' }}>Nhân viên</option>
            </select>
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select" style="border-radius:8px;font-size:13.5px;border:1px solid var(--border);padding:10px 14px;">
                <option value="">Tất cả trạng thái</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Hoạt động</option>
                <option value="banned" {{ request('status') == 'banned' ? 'selected' : '' }}>Bị khóa</option>
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn-admin-primary flex-1"><i class="fas fa-search me-1"></i>Lọc</button>
            <a href="{{ route('admin.staff.index') }}" class="btn-admin-secondary"><i class="fas fa-times"></i></a>
        </div>
    </form>
</div>

{{-- ─── Table ─── --}}
<div class="admin-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h6 class="fw-700 mb-0">{{ $staff->total() }} nhân viên</h6>
    </div>
    <div style="overflow-x:auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Nhân viên</th>
                    <th>SĐT</th>
                    <th>Vai trò</th>
                    <th>Tham gia</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($staff as $member)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <img src="{{ $member->avatar_url }}" alt=""
                                 style="width:40px;height:40px;border-radius:50%;object-fit:cover;border:2px solid var(--border);">
                            <div>
                                <div style="font-weight:600;font-size:13.5px;">{{ $member->name }}</div>
                                <div style="font-size:12px;color:var(--text-muted);">{{ $member->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="font-size:13px;">{{ $member->phone ?? '—' }}</td>
                    <td>
                        @php $roleName = $member->getRoleNames()->first(); @endphp
                        @if($roleName === 'super-admin')
                            <span class="badge" style="background:linear-gradient(135deg,#7c3aed,#4f46e5);color:#fff;padding:4px 10px;border-radius:20px;font-size:11px;font-weight:600;">
                                <i class="fas fa-crown me-1"></i>Super Admin
                            </span>
                        @elseif($roleName === 'admin')
                            <span class="badge" style="background:linear-gradient(135deg,#9333ea,#7c3aed);color:#fff;padding:4px 10px;border-radius:20px;font-size:11px;font-weight:600;">
                                <i class="fas fa-shield-alt me-1"></i>Admin
                            </span>
                        @elseif($roleName === 'manager')
                            <span class="badge" style="background:linear-gradient(135deg,#0284c7,#0ea5e9);color:#fff;padding:4px 10px;border-radius:20px;font-size:11px;font-weight:600;">
                                <i class="fas fa-user-tie me-1"></i>Quản lý
                            </span>
                        @else
                            <span class="badge" style="background:linear-gradient(135deg,#059669,#10b981);color:#fff;padding:4px 10px;border-radius:20px;font-size:11px;font-weight:600;">
                                <i class="fas fa-user me-1"></i>Nhân viên
                            </span>
                        @endif
                    </td>
                    <td style="font-size:12px;color:var(--text-muted);">{{ $member->created_at->format('d/m/Y') }}</td>
                    <td>
                        <span class="status-badge {{ $member->status }}">
                            {{ match($member->status) { 'active' => 'Hoạt động', 'banned' => 'Bị khóa', default => 'Chưa xác thực' } }}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            @if(!$member->hasRole('super-admin') && !$member->hasRole('admin'))
                            <a href="{{ route('admin.staff.edit', $member) }}" class="btn-admin-secondary" title="Chỉnh sửa">
                                <i class="fas fa-edit"></i>
                            </a>
                            @if($member->id !== auth()->id())
                            <button type="button"
                                class="btn-admin-{{ $member->status === 'banned' ? 'primary' : 'warning' }}"
                                onclick="toggleStaff({{ $member->id }}, '{{ $member->status }}')"
                                title="{{ $member->status === 'banned' ? 'Mở khóa' : 'Khóa tạm thời' }}"
                                style="background:{{ $member->status === 'banned' ? '' : 'linear-gradient(135deg,#f59e0b,#d97706)' }};border-color:{{ $member->status === 'banned' ? '' : '#f59e0b' }};">
                                <i class="fas fa-{{ $member->status === 'banned' ? 'unlock' : 'user-slash' }}"></i>
                            </button>
                            <form action="{{ route('admin.staff.destroy', $member) }}" method="POST" style="display:inline;"
                                  onsubmit="return confirm('Xóa nhân viên {{ $member->name }}? Hành động này không thể hoàn tác!')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-admin-danger" title="Xóa">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endif
                            @else
                            <span style="font-size:12px;color:var(--text-muted);padding:6px 10px;">Hệ thống</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;padding:60px;color:var(--text-muted);">
                        <i class="fas fa-users-cog" style="font-size:40px;display:block;margin-bottom:12px;opacity:.3;"></i>
                        Chưa có nhân viên nào
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($staff->hasPages())
    <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
        <small style="color:var(--text-muted);">Hiển thị {{ $staff->firstItem() }}–{{ $staff->lastItem() }} / {{ $staff->total() }}</small>
        <div class="admin-pagination">{{ $staff->links() }}</div>
    </div>
    @endif
</div>

{{-- ─── Permission Info ─── --}}
<div class="admin-card mt-4" style="border-left:4px solid var(--gold);">
    <h6 class="fw-700 mb-3"><i class="fas fa-info-circle me-2" style="color:var(--gold);"></i>Phân quyền hệ thống</h6>
    <div class="row g-3">
        <div class="col-md-4">
            <div style="padding:16px;background:rgba(124,58,237,.07);border-radius:10px;border:1px solid rgba(124,58,237,.2);">
                <div style="font-weight:700;color:#7c3aed;margin-bottom:8px;"><i class="fas fa-crown me-1"></i> Admin / Super Admin</div>
                <ul style="font-size:12px;color:var(--text-muted);margin:0;padding-left:16px;line-height:1.8;">
                    <li>Toàn quyền hệ thống</li>
                    <li>Quản lý nhân viên</li>
                    <li>Cài đặt & Báo cáo</li>
                    <li>Mã giảm giá & Flash sale</li>
                </ul>
            </div>
        </div>
        <div class="col-md-4">
            <div style="padding:16px;background:rgba(14,165,233,.07);border-radius:10px;border:1px solid rgba(14,165,233,.2);">
                <div style="font-weight:700;color:#0ea5e9;margin-bottom:8px;"><i class="fas fa-user-tie me-1"></i> Quản lý (Manager)</div>
                <ul style="font-size:12px;color:var(--text-muted);margin:0;padding-left:16px;line-height:1.8;">
                    <li>Toàn quyền hệ thống</li>
                    <li>Quản lý nhân viên</li>
                    <li>Cài đặt & Báo cáo</li>
                    <li>Mã giảm giá & Flash sale</li>
                </ul>
            </div>
        </div>
        <div class="col-md-4">
            <div style="padding:16px;background:rgba(16,185,129,.07);border-radius:10px;border:1px solid rgba(16,185,129,.2);">
                <div style="font-weight:700;color:#10b981;margin-bottom:8px;"><i class="fas fa-user me-1"></i> Nhân viên (Staff)</div>
                <ul style="font-size:12px;color:var(--text-muted);margin:0;padding-left:16px;line-height:1.8;">
                    <li>Sản phẩm, danh mục, thương hiệu</li>
                    <li>Xử lý đơn hàng</li>
                    <li>Duyệt đánh giá</li>
                    <li><del>Khuyến mãi, báo cáo, cài đặt</del></li>
                </ul>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
function toggleStaff(id, currentStatus) {
    const action = currentStatus === 'banned' ? 'Mở khóa' : 'Khóa tạm thời';
    if (!confirm(`${action} tài khoản nhân viên này?`)) return;
    $.ajax({
        url: `/admin/nhan-vien/${id}/trang-thai`,
        method: 'PATCH',
    }).done(res => {
        if (res.success) location.reload();
        else alert(res.message || 'Thao tác thất bại!');
    }).fail(() => alert('Có lỗi xảy ra!'));
}
</script>
@endsection
