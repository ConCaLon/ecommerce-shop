@extends('layouts.admin')

@section('title', 'Flash Sale')
@section('page-title', 'Flash Sale')
@section('breadcrumb', 'Flash Sale')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-700 mb-0">⚡ Quản Lý Flash Sale</h5>
    <a href="{{ route('admin.flash-sales.create') }}" class="btn-admin-primary">
        <i class="fas fa-plus me-1"></i>Tạo Flash Sale
    </a>
</div>

<div class="admin-card">
    <div style="overflow-x:auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Tên Flash Sale</th>
                    <th>Bắt đầu</th>
                    <th>Kết thúc</th>
                    <th>Sản phẩm</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($flashSales as $sale)
                @php
                $now = now();
                $isActive = $sale->status === 'active' && $now->between($sale->starts_at, $sale->ends_at);
                $isEnded = $now->gt($sale->ends_at);
                $isPending = $now->lt($sale->starts_at);
                @endphp
                <tr>
                    <td>
                        <div style="font-weight:700;font-size:13.5px;">{{ $sale->name }}</div>
                        @if($isActive)
                            <span style="font-size:11px;background:rgba(39,174,96,.1);color:var(--green);padding:2px 8px;border-radius:10px;font-weight:600;">🔴 Đang diễn ra</span>
                        @elseif($isPending)
                            <span style="font-size:11px;background:rgba(243,156,18,.1);color:var(--orange);padding:2px 8px;border-radius:10px;font-weight:600;">⏳ Sắp diễn ra</span>
                        @else
                            <span style="font-size:11px;background:rgba(108,117,125,.1);color:var(--text-muted);padding:2px 8px;border-radius:10px;font-weight:600;">✓ Đã kết thúc</span>
                        @endif
                    </td>
                    <td style="font-size:13px;">{{ $sale->starts_at->format('d/m/Y H:i') }}</td>
                    <td style="font-size:13px;">{{ $sale->ends_at->format('d/m/Y H:i') }}</td>
                    <td style="text-align:center;font-weight:600;font-size:13px;">{{ $sale->products_count }}</td>
                    <td>
                        <span class="status-badge {{ $sale->status }}">{{ $sale->status === 'active' ? 'Hoạt động' : 'Tắt' }}</span>
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.flash-sales.show', $sale) }}" class="btn-admin-secondary" title="Quản lý sản phẩm">
                                <i class="fas fa-list"></i>
                            </a>
                            <a href="{{ route('admin.flash-sales.edit', $sale) }}" class="btn-admin-secondary" title="Chỉnh sửa">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.flash-sales.destroy', $sale) }}" style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-admin-danger" onclick="return confirm('Xóa Flash Sale này?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;padding:60px;color:var(--text-muted);">
                        <i class="fas fa-bolt" style="font-size:40px;display:block;margin-bottom:12px;opacity:.3;"></i>
                        Chưa có Flash Sale nào
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($flashSales->hasPages())
    <div class="mt-4 admin-pagination">{{ $flashSales->links() }}</div>
    @endif
</div>
@endsection
