@extends('layouts.admin')

@section('title', 'Quản Lý Đơn Hàng')
@section('page-title', 'Đơn Hàng')
@section('breadcrumb', 'Đơn Hàng')

@section('content')

{{-- ─── Status Tabs ─── --}}
<div class="admin-card mb-4">
    <div class="d-flex flex-wrap gap-3">
        @php
        $statusList = [
            '' => ['label' => 'Tất cả', 'color' => 'var(--text)'],
            'pending' => ['label' => 'Chờ xác nhận', 'color' => '#D4850A'],
            'confirmed' => ['label' => 'Đã xác nhận', 'color' => '#2980B9'],
            'processing' => ['label' => 'Đang xử lý', 'color' => '#9B59B6'],
            'shipping' => ['label' => 'Đang giao', 'color' => '#1ABC9C'],
            'delivered' => ['label' => 'Đã giao', 'color' => '#27AE60'],
            'cancelled' => ['label' => 'Đã hủy', 'color' => '#E74C3C'],
        ];
        @endphp
        @foreach($statusList as $key => $info)
        <a href="{{ route('admin.orders.index') }}{{ $key ? '?status='.$key : '' }}"
           style="padding:8px 16px;border-radius:20px;font-size:13px;font-weight:600;text-decoration:none;
                  {{ request('status') == $key && ($key || !request('status'))
                    ? 'background:rgba(201,168,76,.15);color:var(--gold);border:1px solid rgba(201,168,76,.3);'
                    : 'background:var(--body-bg);color:var(--text-muted);border:1px solid var(--border);' }}">
            {{ $info['label'] }}
            @if(isset($statusCounts[$key]))
            <span style="margin-left:4px;background:{{ request('status') == $key ? 'var(--gold)' : 'rgba(0,0,0,.08)' }};color:{{ request('status') == $key ? '#000' : 'var(--text)' }};font-size:11px;padding:1px 7px;border-radius:10px;">
                {{ $statusCounts[$key] ?? 0 }}
            </span>
            @endif
        </a>
        @endforeach
    </div>
</div>

{{-- ─── Filters ─── --}}
<div class="admin-card mb-4">
    <form method="GET" action="{{ route('admin.orders.index') }}" class="row g-3 align-items-end">
        @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
        <div class="col-md-4">
            <div class="admin-search">
                <i class="fas fa-search search-icon"></i>
                <input type="text" name="search" class="form-control" placeholder="Tìm mã đơn, tên, SĐT..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-md-3">
            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" placeholder="Từ ngày" style="border-radius:8px;font-size:13.5px;border:1px solid var(--border);padding:10px 14px;">
        </div>
        <div class="col-md-3">
            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" placeholder="Đến ngày" style="border-radius:8px;font-size:13.5px;border:1px solid var(--border);padding:10px 14px;">
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn-admin-primary flex-1"><i class="fas fa-search"></i> Lọc</button>
            <a href="{{ route('admin.orders.index') }}" class="btn-admin-secondary"><i class="fas fa-times"></i></a>
        </div>
    </form>
</div>

{{-- ─── Export ─── --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="fw-700 mb-0">{{ $orders->total() }} đơn hàng</h6>
    <a href="{{ route('admin.orders.export') }}{{ request()->query() ? '?'.http_build_query(request()->query()) : '' }}"
       class="btn-admin-secondary">
        <i class="fas fa-download me-1"></i>Xuất CSV
    </a>
</div>

{{-- ─── Table ─── --}}
<div class="admin-card">
    <div style="overflow-x:auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Mã đơn</th>
                    <th>Khách hàng</th>
                    <th>Sản phẩm</th>
                    <th>Tổng tiền</th>
                    <th>Thanh toán</th>
                    <th>Trạng thái</th>
                    <th>Ngày đặt</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                <tr>
                    <td>
                        <div style="font-weight:700;font-size:13px;font-family:monospace;">{{ $order->order_code }}</div>
                    </td>
                    <td>
                        <div style="font-size:13.5px;font-weight:600;">{{ $order->shipping_name }}</div>
                        <div style="font-size:12px;color:var(--text-muted);">{{ $order->shipping_phone }}</div>
                    </td>
                    <td style="font-size:12px;color:var(--text-muted);">{{ $order->details->count() }} sản phẩm</td>
                    <td>
                        <div style="font-weight:700;color:var(--gold);font-size:13.5px;">{{ vnd($order->total) }}</div>
                        @if($order->discount_amount > 0)
                            <div style="font-size:11px;color:var(--green);">-{{ vnd($order->discount_amount) }}</div>
                        @endif
                    </td>
                    <td>
                        <div style="font-size:12px;">{{ $order->payment_method_label }}</div>
                        <span style="font-size:11px;padding:2px 8px;border-radius:10px;font-weight:600;
                            {{ $order->payment_status === 'paid' ? 'background:rgba(39,174,96,.1);color:var(--green)' : 'background:rgba(243,156,18,.1);color:var(--orange)' }}">
                            {{ match($order->payment_status) { 'paid' => 'Đã TT', 'failed' => 'Thất bại', default => 'Chưa TT' } }}
                        </span>
                    </td>
                    <td>
                        <span class="status-badge {{ $order->status }}">{{ $order->status_label }}</span>
                    </td>
                    <td style="font-size:12px;color:var(--text-muted);">
                        {{ $order->created_at->format('d/m/Y') }}<br>
                        {{ $order->created_at->format('H:i') }}
                    </td>
                    <td>
                        <a href="{{ route('admin.orders.show', $order) }}" class="btn-admin-secondary" title="Xem chi tiết">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center;padding:60px;color:var(--text-muted);">
                        <i class="fas fa-shopping-cart" style="font-size:40px;display:block;margin-bottom:12px;opacity:.3;"></i>
                        Không có đơn hàng nào
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($orders->hasPages())
    <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
        <small style="color:var(--text-muted);">Hiển thị {{ $orders->firstItem() }}–{{ $orders->lastItem() }} / {{ $orders->total() }}</small>
        <div class="admin-pagination">{{ $orders->links() }}</div>
    </div>
    @endif
</div>
@endsection
