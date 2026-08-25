@extends('layouts.admin')

@section('title', 'Sản Phẩm Bán Chạy')
@section('page-title', 'Sản Phẩm Bán Chạy')
@section('breadcrumb', 'Báo Cáo / Sản phẩm')

@section('content')

{{-- ─── Sub Nav --}}
<div class="d-flex gap-2 mb-4">
    <a href="{{ route('admin.reports.revenue') }}" class="btn-admin-secondary"><i class="fas fa-chart-bar me-1"></i>Doanh Thu</a>
    <a href="{{ route('admin.reports.top-products') }}" class="btn-admin-primary"><i class="fas fa-trophy me-1"></i>Sản Phẩm</a>
    <a href="{{ route('admin.reports.customers') }}" class="btn-admin-secondary"><i class="fas fa-users me-1"></i>Khách Hàng</a>
</div>

{{-- ─── Filters ─── --}}
<div class="admin-card mb-4">
    <form method="GET" action="{{ route('admin.reports.top-products') }}" class="row g-3 align-items-end">
        <div class="col-md-3">
            <label style="font-size:13px;font-weight:600;display:block;margin-bottom:6px;">Kỳ (ngày gần nhất)</label>
            <select name="period" class="form-select" style="border-radius:8px;font-size:13.5px;border:1px solid var(--border);padding:10px 14px;">
                <option value="7" {{ $period == 7 ? 'selected' : '' }}>7 ngày</option>
                <option value="30" {{ $period == 30 ? 'selected' : '' }}>30 ngày</option>
                <option value="90" {{ $period == 90 ? 'selected' : '' }}>90 ngày</option>
                <option value="365" {{ $period == 365 ? 'selected' : '' }}>1 năm</option>
            </select>
        </div>
        <div class="col-md-3">
            <label style="font-size:13px;font-weight:600;display:block;margin-bottom:6px;">Hiển thị</label>
            <select name="limit" class="form-select" style="border-radius:8px;font-size:13.5px;border:1px solid var(--border);padding:10px 14px;">
                <option value="10" {{ $limit == 10 ? 'selected' : '' }}>Top 10</option>
                <option value="20" {{ $limit == 20 ? 'selected' : '' }}>Top 20</option>
                <option value="50" {{ $limit == 50 ? 'selected' : '' }}>Top 50</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn-admin-primary w-100"><i class="fas fa-search me-1"></i>Xem</button>
        </div>
    </form>
</div>

{{-- ─── Table ─── --}}
<div class="admin-card">
    <h6 class="fw-700 mb-4">Top {{ $limit }} Sản Phẩm Bán Chạy ({{ $period }} ngày qua)</h6>
    <div style="overflow-x:auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Sản phẩm</th>
                    <th>SKU</th>
                    <th style="text-align:right;">Đã bán</th>
                    <th style="text-align:right;">Doanh thu</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topProducts as $i => $product)
                <tr>
                    <td>
                        <span style="font-size:16px;font-weight:700;color:{{ $i < 3 ? 'var(--gold)' : 'var(--text-muted)' }};">
                            {{ $i + 1 }}
                        </span>
                    </td>
                    <td style="font-weight:600;font-size:13.5px;">{{ $product->name }}</td>
                    <td style="font-size:12px;color:var(--text-muted);font-family:monospace;">{{ $product->sku }}</td>
                    <td style="text-align:right;font-weight:700;font-size:13.5px;">{{ number_format($product->total_sold) }}</td>
                    <td style="text-align:right;font-weight:700;color:var(--gold);">{{ number_format($product->total_revenue, 0, ',', '.').'đ' }}</td>
                </tr>
                @empty
                <tr><td colspan="5" style="text-align:center;padding:40px;color:var(--text-muted);">Chưa có dữ liệu</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
