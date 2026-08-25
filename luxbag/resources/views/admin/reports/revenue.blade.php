@extends('layouts.admin')

@section('title', 'Báo Cáo Doanh Thu')
@section('page-title', 'Báo Cáo Doanh Thu')
@section('breadcrumb', 'Báo Cáo / Doanh thu')

@section('content')

{{-- ─── Sub Nav --}}
<div class="d-flex gap-2 mb-4">
    <a href="{{ route('admin.reports.revenue') }}" class="btn-admin-primary" style="{{ request()->routeIs('admin.reports.revenue') ? '' : 'background:var(--body-bg);color:var(--text);border:1px solid var(--border);' }}">
        <i class="fas fa-chart-bar me-1"></i>Doanh Thu
    </a>
    <a href="{{ route('admin.reports.top-products') }}" class="btn-admin-secondary">
        <i class="fas fa-trophy me-1"></i>Sản Phẩm
    </a>
    <a href="{{ route('admin.reports.customers') }}" class="btn-admin-secondary">
        <i class="fas fa-users me-1"></i>Khách Hàng
    </a>
    <a href="{{ route('admin.reports.export') }}?type=revenue" class="btn-admin-secondary ms-auto">
        <i class="fas fa-download me-1"></i>Xuất CSV
    </a>
</div>

{{-- ─── Filters ─── --}}
<div class="admin-card mb-4">
    <form method="GET" action="{{ route('admin.reports.revenue') }}" class="row g-3 align-items-end">
        <div class="col-md-3">
            <label style="font-size:13px;font-weight:600;display:block;margin-bottom:6px;">Kỳ báo cáo</label>
            <select name="period" class="form-select" style="border-radius:8px;font-size:13.5px;border:1px solid var(--border);padding:10px 14px;">
                <option value="monthly" {{ $period == 'monthly' ? 'selected' : '' }}>Theo tháng</option>
                <option value="daily" {{ $period == 'daily' ? 'selected' : '' }}>Theo ngày</option>
            </select>
        </div>
        <div class="col-md-2">
            <label style="font-size:13px;font-weight:600;display:block;margin-bottom:6px;">Năm</label>
            <select name="year" class="form-select" style="border-radius:8px;font-size:13.5px;border:1px solid var(--border);padding:10px 14px;">
                @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </div>
        @if($period == 'daily')
        <div class="col-md-2">
            <label style="font-size:13px;font-weight:600;display:block;margin-bottom:6px;">Tháng</label>
            <select name="month" class="form-select" style="border-radius:8px;font-size:13.5px;border:1px solid var(--border);padding:10px 14px;">
                @for($m=1;$m<=12;$m++)
                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>Tháng {{ $m }}</option>
                @endfor
            </select>
        </div>
        @endif
        <div class="col-md-2">
            <button type="submit" class="btn-admin-primary w-100"><i class="fas fa-search me-1"></i>Xem</button>
        </div>
    </form>
</div>

{{-- ─── Summary Cards ─── --}}
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon gold"><i class="fas fa-coins"></i></div>
            <div>
                <div class="stat-value">{{ number_format($totalRevenue / 1000000, 1) }}M</div>
                <div class="stat-label">Tổng doanh thu</div>
                <small style="color:var(--text-muted);font-size:11px;">Đơn đã giao thành công</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-shopping-cart"></i></div>
            <div>
                <div class="stat-value">{{ number_format($totalOrders) }}</div>
                <div class="stat-label">Tổng đơn hàng</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-receipt"></i></div>
            <div>
                <div class="stat-value">{{ $totalOrders > 0 ? number_format($totalRevenue / $totalOrders / 1000).'K' : '0' }}</div>
                <div class="stat-label">Giá trị TB/đơn</div>
            </div>
        </div>
    </div>
</div>

{{-- ─── Chart ─── --}}
<div class="admin-card mb-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h6 class="fw-700 mb-0">
            Biểu Đồ Doanh Thu
            @if($period == 'daily') (Tháng {{ $month }}/{{ $year }}) @else (Năm {{ $year }}) @endif
        </h6>
    </div>
    <canvas id="revenueChart" height="80"></canvas>
</div>

{{-- ─── Table ─── --}}
<div class="admin-card">
    <h6 class="fw-700 mb-4">Chi Tiết Doanh Thu</h6>
    <div style="overflow-x:auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>{{ $period == 'daily' ? 'Ngày' : 'Tháng' }}</th>
                    <th style="text-align:right;">Số đơn</th>
                    <th style="text-align:right;">Doanh thu</th>
                    <th style="text-align:right;">TB/đơn</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $row)
                <tr>
                    <td>{{ $period == 'daily' ? "Ngày {$row->day}/{$month}/{$year}" : "Tháng {$row->month}/{$year}" }}</td>
                    <td style="text-align:right;font-weight:600;">{{ number_format($row->orders) }}</td>
                    <td style="text-align:right;font-weight:700;color:var(--gold);">{{ number_format($row->revenue, 0, ',', '.').'đ' }}</td>
                    <td style="text-align:right;color:var(--text-muted);font-size:12px;">
                        {{ $row->orders > 0 ? number_format($row->revenue / $row->orders, 0, ',', '.').'đ' : '—' }}
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" style="text-align:center;padding:40px;color:var(--text-muted);">Không có dữ liệu</td></tr>
                @endforelse
            </tbody>
            @if($data->count() > 0)
            <tfoot>
                <tr style="background:var(--body-bg);">
                    <td style="font-weight:700;">Tổng</td>
                    <td style="text-align:right;font-weight:700;">{{ number_format($totalOrders) }}</td>
                    <td style="text-align:right;font-weight:700;color:var(--gold);">{{ number_format($totalRevenue, 0, ',', '.').'đ' }}</td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

@endsection

@section('scripts')
<script>
new Chart(document.getElementById('revenueChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: @json($labels),
        datasets: [{
            label: 'Doanh thu (đ)',
            data: @json($data->pluck('revenue')),
            backgroundColor: 'rgba(201,168,76,0.25)',
            borderColor: '#C9A84C',
            borderWidth: 2,
            borderRadius: 6,
        }, {
            label: 'Số đơn',
            data: @json($data->pluck('orders')),
            type: 'line',
            borderColor: '#3498DB',
            borderWidth: 2,
            pointRadius: 4,
            pointBackgroundColor: '#3498DB',
            yAxisID: 'y1',
            tension: 0.4,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'top' },
            tooltip: {
                callbacks: {
                    label: ctx => ctx.datasetIndex === 0
                        ? 'DT: ' + new Intl.NumberFormat('vi-VN').format(ctx.raw) + 'đ'
                        : 'Đơn: ' + ctx.raw
                }
            }
        },
        scales: {
            y: { ticks: { callback: v => v >= 1e6 ? (v/1e6).toFixed(1)+'M' : v/1000+'K' } },
            y1: { position: 'right', grid: { display: false } },
            x: { grid: { display: false } }
        }
    }
});
</script>
@endsection
