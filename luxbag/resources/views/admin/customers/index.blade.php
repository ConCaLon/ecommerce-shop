@extends('layouts.admin')

@section('title', 'Khách Hàng')
@section('page-title', 'Khách Hàng')
@section('breadcrumb', 'Khách Hàng')

@section('content')

{{-- ─── Filters ─── --}}
<div class="admin-card mb-4">
    <form method="GET" action="{{ route('admin.customers.index') }}" class="row g-3 align-items-end">
        <div class="col-md-5">
            <div class="admin-search">
                <i class="fas fa-search search-icon"></i>
                <input type="text" name="search" class="form-control" placeholder="Tìm tên, email, SĐT..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select" style="border-radius:8px;font-size:13.5px;border:1px solid var(--border);padding:10px 14px;">
                <option value="">Tất cả trạng thái</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Hoạt động</option>
                <option value="banned" {{ request('status') == 'banned' ? 'selected' : '' }}>Bị khóa</option>
                <option value="unverified" {{ request('status') == 'unverified' ? 'selected' : '' }}>Chưa xác thực</option>
            </select>
        </div>
        <div class="col-md-4 d-flex gap-2">
            <button type="submit" class="btn-admin-primary flex-1"><i class="fas fa-search me-1"></i>Lọc</button>
            <a href="{{ route('admin.customers.index') }}" class="btn-admin-secondary"><i class="fas fa-times"></i></a>
        </div>
    </form>
</div>

{{-- ─── Table ─── --}}
<div class="admin-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h6 class="fw-700 mb-0">{{ $customers->total() }} khách hàng</h6>
    </div>
    <div style="overflow-x:auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Khách hàng</th>
                    <th>SĐT</th>
                    <th>Đơn hàng</th>
                    <th>Tham gia</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <img src="{{ $customer->avatar_url }}" alt=""
                                 style="width:38px;height:38px;border-radius:50%;object-fit:cover;border:2px solid var(--border);">
                            <div>
                                <div style="font-weight:600;font-size:13.5px;">{{ $customer->name }}</div>
                                <div style="font-size:12px;color:var(--text-muted);">{{ $customer->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="font-size:13px;">{{ $customer->phone ?? '—' }}</td>
                    <td style="font-size:13px;font-weight:600;text-align:center;">{{ $customer->orders_count }}</td>
                    <td style="font-size:12px;color:var(--text-muted);">{{ $customer->created_at->format('d/m/Y') }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <span class="status-badge {{ $customer->status }}">
                                {{ match($customer->status) { 'active' => 'Hoạt động', 'banned' => 'Bị khóa', default => 'Chưa xác thực' } }}
                            </span>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.customers.show', $customer) }}" class="btn-admin-secondary" title="Xem chi tiết">
                                <i class="fas fa-eye"></i>
                            </a>
                            <button type="button" class="btn-admin-{{ $customer->status === 'banned' ? 'primary' : 'danger' }}"
                                onclick="toggleCustomer({{ $customer->id }}, '{{ $customer->status }}')"
                                title="{{ $customer->status === 'banned' ? 'Mở khóa' : 'Khóa' }}">
                                <i class="fas fa-{{ $customer->status === 'banned' ? 'unlock' : 'ban' }}"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;padding:60px;color:var(--text-muted);">
                        <i class="fas fa-users" style="font-size:40px;display:block;margin-bottom:12px;opacity:.3;"></i>
                        Không tìm thấy khách hàng nào
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($customers->hasPages())
    <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
        <small style="color:var(--text-muted);">Hiển thị {{ $customers->firstItem() }}–{{ $customers->lastItem() }} / {{ $customers->total() }}</small>
        <div class="admin-pagination">{{ $customers->links() }}</div>
    </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
function toggleCustomer(id, currentStatus) {
    const action = currentStatus === 'banned' ? 'Mở khóa' : 'Khóa';
    if (!confirm(`${action} tài khoản này?`)) return;
    $.ajax({
        url: `/admin/khach-hang/${id}/trang-thai`,
        method: 'PATCH',
    }).done(res => {
        if (res.success) location.reload();
    });
}
</script>
@endsection
