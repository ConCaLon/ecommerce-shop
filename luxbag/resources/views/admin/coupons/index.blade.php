@extends('layouts.admin')

@section('title', 'Mã Giảm Giá')
@section('page-title', 'Mã Giảm Giá')
@section('breadcrumb', 'Mã Giảm Giá')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-700 mb-0">Quản Lý Mã Giảm Giá</h5>
    <a href="{{ route('admin.coupons.create') }}" class="btn-admin-primary">
        <i class="fas fa-plus me-1"></i>Tạo Mã Mới
    </a>
</div>

<div class="admin-card">
    <div style="overflow-x:auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Mã Code</th>
                    <th>Loại</th>
                    <th>Giá trị</th>
                    <th>Đã dùng</th>
                    <th>Giới hạn</th>
                    <th>Hết hạn</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($coupons as $coupon)
                <tr>
                    <td>
                        <div style="font-family:monospace;font-size:14px;font-weight:700;letter-spacing:1px;color:var(--gold);">
                            {{ $coupon->code }}
                        </div>
                        @if($coupon->description)
                        <div style="font-size:11px;color:var(--text-muted);">{{ $coupon->description }}</div>
                        @endif
                    </td>
                    <td>
                        <span style="font-size:12px;padding:3px 10px;border-radius:10px;font-weight:600;
                            {{ $coupon->type === 'percent' ? 'background:rgba(52,152,219,.1);color:#2980B9' : 'background:rgba(39,174,96,.1);color:var(--green)' }}">
                            {{ $coupon->type === 'percent' ? 'Phần trăm' : 'Cố định' }}
                        </span>
                    </td>
                    <td style="font-weight:700;font-size:13.5px;">
                        {{ $coupon->type === 'percent' ? $coupon->value.'%' : vnd($coupon->value) }}
                        @if($coupon->min_order_amount > 0)
                        <br><span style="font-size:11px;color:var(--text-muted);">Tối thiểu: {{ vnd($coupon->min_order_amount) }}</span>
                        @endif
                    </td>
                    <td style="text-align:center;">
                        <span style="font-size:13px;font-weight:600;">{{ $coupon->usages_count }}</span>
                    </td>
                    <td style="text-align:center;font-size:13px;">{{ $coupon->max_uses ?? '∞' }}</td>
                    <td style="font-size:12px;color:var(--text-muted);">
                        {{ $coupon->expires_at ? $coupon->expires_at->format('d/m/Y') : 'Không hết hạn' }}
                        @if($coupon->expires_at && $coupon->expires_at->isPast())
                            <br><span style="color:var(--red);font-size:11px;">Đã hết hạn</span>
                        @endif
                    </td>
                    <td><span class="status-badge {{ $coupon->status }}">{{ match($coupon->status) { 'active' => 'Hoạt động', 'inactive' => 'Tắt', default => 'Hết hạn' } }}</span></td>
                    <td>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.coupons.edit', $coupon) }}" class="btn-admin-secondary" title="Sửa">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}" style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-admin-danger" onclick="return confirm('Xóa mã giảm giá này?')" title="Xóa">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center;padding:60px;color:var(--text-muted);">
                        <i class="fas fa-ticket-alt" style="font-size:40px;display:block;margin-bottom:12px;opacity:.3;"></i>
                        Chưa có mã giảm giá nào
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($coupons->hasPages())
    <div class="mt-4 admin-pagination">{{ $coupons->links() }}</div>
    @endif
</div>
@endsection
