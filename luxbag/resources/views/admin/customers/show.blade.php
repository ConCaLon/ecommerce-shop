@extends('layouts.admin')

@section('title', $user->name.' — Khách Hàng')
@section('page-title', 'Hồ Sơ Khách Hàng')
@section('breadcrumb', 'Khách Hàng / '.$user->name)

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-700 mb-0">Hồ Sơ: {{ $user->name }}</h5>
    <a href="{{ route('admin.customers.index') }}" class="btn-admin-secondary">
        <i class="fas fa-arrow-left me-1"></i>Quay lại
    </a>
</div>

<div class="row g-4">
    {{-- ─── Profile Info ─── --}}
    <div class="col-lg-4">
        <div class="admin-card mb-4" style="text-align:center;">
            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}"
                 style="width:80px;height:80px;border-radius:50%;object-fit:cover;border:3px solid var(--gold);margin-bottom:12px;">
            <h6 class="fw-700" style="font-size:16px;">{{ $user->name }}</h6>
            <div style="font-size:13px;color:var(--text-muted);">{{ $user->email }}</div>
            <div class="mt-3">
                <span class="status-badge {{ $user->status }}">
                    {{ match($user->status) { 'active' => 'Hoạt động', 'banned' => 'Bị khóa', default => 'Chưa xác thực' } }}
                </span>
            </div>
            @if(!$user->isAdmin())
            <button type="button" class="btn-admin-{{ $user->status === 'banned' ? 'primary' : 'danger' }} w-100 mt-3"
                onclick="toggleStatus({{ $user->id }}, '{{ $user->status }}')">
                <i class="fas fa-{{ $user->status === 'banned' ? 'unlock' : 'ban' }} me-1"></i>
                {{ $user->status === 'banned' ? 'Mở khóa tài khoản' : 'Khóa tài khoản' }}
            </button>
            @endif
        </div>

        <div class="admin-card">
            <h6 class="fw-700 mb-3">Thông Tin</h6>
            @if($user->phone)
            <div class="d-flex justify-content-between mb-2">
                <span style="font-size:13px;color:var(--text-muted);"><i class="fas fa-phone me-2"></i>SĐT</span>
                <span style="font-size:13px;font-weight:600;">{{ $user->phone }}</span>
            </div>
            @endif
            @if($user->date_of_birth)
            <div class="d-flex justify-content-between mb-2">
                <span style="font-size:13px;color:var(--text-muted);"><i class="fas fa-birthday-cake me-2"></i>Sinh nhật</span>
                <span style="font-size:13px;font-weight:600;">{{ \Carbon\Carbon::parse($user->date_of_birth)->format('d/m/Y') }}</span>
            </div>
            @endif
            <div class="d-flex justify-content-between mb-2">
                <span style="font-size:13px;color:var(--text-muted);"><i class="fas fa-calendar me-2"></i>Tham gia</span>
                <span style="font-size:13px;font-weight:600;">{{ $user->created_at->format('d/m/Y') }}</span>
            </div>
        </div>
    </div>

    {{-- ─── Stats + Orders ─── --}}
    <div class="col-lg-8">
        {{-- Stats --}}
        <div class="row g-3 mb-4">
            <div class="col-4">
                <div class="stat-card">
                    <div class="stat-icon gold"><i class="fas fa-shopping-bag"></i></div>
                    <div>
                        <div class="stat-value">{{ $user->orders->count() }}</div>
                        <div class="stat-label">Đơn hàng</div>
                    </div>
                </div>
            </div>
            <div class="col-4">
                <div class="stat-card">
                    <div class="stat-icon green"><i class="fas fa-coins"></i></div>
                    <div>
                        <div class="stat-value">{{ number_format($totalSpent / 1000000, 1) }}M</div>
                        <div class="stat-label">Đã chi tiêu</div>
                    </div>
                </div>
            </div>
            <div class="col-4">
                <div class="stat-card">
                    <div class="stat-icon blue"><i class="fas fa-check-circle"></i></div>
                    <div>
                        <div class="stat-value">{{ $user->orders->where('status','delivered')->count() }}</div>
                        <div class="stat-label">Đơn hoàn thành</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent Orders --}}
        <div class="admin-card">
            <h6 class="fw-700 mb-4">Đơn Hàng Gần Đây</h6>
            <div style="overflow-x:auto;">
                <table class="admin-table">
                    <thead>
                        <tr><th>Mã đơn</th><th>Tổng tiền</th><th>Trạng thái</th><th>Ngày đặt</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse($user->orders as $order)
                        <tr>
                            <td style="font-weight:700;font-family:monospace;font-size:13px;">{{ $order->order_code }}</td>
                            <td style="font-weight:700;color:var(--gold);">{{ vnd($order->total) }}</td>
                            <td><span class="status-badge {{ $order->status }}">{{ $order->status_label }}</span></td>
                            <td style="font-size:12px;color:var(--text-muted);">{{ $order->created_at->format('d/m/Y') }}</td>
                            <td>
                                <a href="{{ route('admin.orders.show', $order) }}" class="btn-admin-secondary" style="padding:5px 10px;">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" style="text-align:center;padding:30px;color:var(--text-muted);">Chưa có đơn hàng</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function toggleStatus(id, currentStatus) {
    const action = currentStatus === 'banned' ? 'Mở khóa' : 'Khóa';
    if (!confirm(`${action} tài khoản này?`)) return;
    $.ajax({ url: `/admin/khach-hang/${id}/trang-thai`, method: 'PATCH' })
     .done(res => { if (res.success) location.reload(); });
}
</script>
@endsection
