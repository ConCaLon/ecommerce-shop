@extends('layouts.admin')

@section('title', 'Báo Cáo Khách Hàng')
@section('page-title', 'Khách Hàng VIP')
@section('breadcrumb', 'Báo Cáo / Khách Hàng')

@section('content')

{{-- ─── Sub Nav --}}
<div class="d-flex gap-2 mb-4">
    <a href="{{ route('admin.reports.revenue') }}" class="btn-admin-secondary"><i class="fas fa-chart-bar me-1"></i>Doanh Thu</a>
    <a href="{{ route('admin.reports.top-products') }}" class="btn-admin-secondary"><i class="fas fa-trophy me-1"></i>Sản Phẩm</a>
    <a href="{{ route('admin.reports.customers') }}" class="btn-admin-primary"><i class="fas fa-users me-1"></i>Khách Hàng</a>
</div>

<div class="admin-card">
    <h6 class="fw-700 mb-4">Top 20 Khách Hàng VIP (Chi Tiêu Nhiều Nhất)</h6>
    <div style="overflow-x:auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Khách hàng</th>
                    <th style="text-align:center;">Số đơn</th>
                    <th style="text-align:right;">Tổng chi tiêu</th>
                    <th>Tham gia</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($topCustomers as $i => $customer)
                <tr>
                    <td>
                        <span style="font-size:16px;font-weight:700;color:{{ $i < 3 ? 'var(--gold)' : 'var(--text-muted)' }};">
                            {{ $i + 1 }}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <img src="{{ $customer->avatar_url }}" alt=""
                                 style="width:36px;height:36px;border-radius:50%;object-fit:cover;border:2px solid {{ $i < 3 ? 'var(--gold)' : 'var(--border)' }};">
                            <div>
                                <div style="font-weight:600;font-size:13.5px;">{{ $customer->name }}</div>
                                <div style="font-size:12px;color:var(--text-muted);">{{ $customer->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="text-align:center;font-weight:600;font-size:13.5px;">{{ number_format($customer->orders_count) }}</td>
                    <td style="text-align:right;font-weight:700;color:var(--gold);font-size:13.5px;">
                        {{ number_format($customer->orders_sum_total, 0, ',', '.').'đ' }}
                    </td>
                    <td style="font-size:12px;color:var(--text-muted);">{{ $customer->created_at->format('d/m/Y') }}</td>
                    <td>
                        <a href="{{ route('admin.customers.show', $customer) }}" class="btn-admin-secondary" style="padding:5px 12px;font-size:12px;">
                            <i class="fas fa-eye me-1"></i>Xem
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center;padding:40px;color:var(--text-muted);">Chưa có dữ liệu</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
