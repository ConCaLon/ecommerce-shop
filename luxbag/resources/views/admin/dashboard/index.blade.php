@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('breadcrumb', 'Dashboard')

@section('content')

{{-- ─── Stats Row ─── --}}
<div class="row g-4 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon gold"><i class="fas fa-coins"></i></div>
            <div>
                <div class="stat-value">{{ number_format($stats['revenue_month'] / 1000000, 1) }}M</div>
                <div class="stat-label">Doanh thu tháng này</div>
                <small style="color:var(--green);font-size:12px;"><i class="fas fa-arrow-up me-1"></i>Tháng {{ date('m') }}</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-shopping-cart"></i></div>
            <div>
                <div class="stat-value">{{ number_format($stats['orders_pending']) }}</div>
                <div class="stat-label">Đơn chờ xác nhận</div>
                <small style="color:var(--orange);font-size:12px;"><i class="fas fa-clock me-1"></i>Cần xử lý</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-users"></i></div>
            <div>
                <div class="stat-value">{{ number_format($stats['total_customers']) }}</div>
                <div class="stat-label">Tổng khách hàng</div>
                <small style="color:var(--green);font-size:12px;"><i class="fas fa-user-plus me-1"></i>+{{ $stats['new_customers'] }} hôm nay</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon {{ $stats['low_stock'] > 0 ? 'red' : 'gold' }}"><i class="fas fa-box"></i></div>
            <div>
                <div class="stat-value">{{ $stats['total_products'] }}</div>
                <div class="stat-label">Sản phẩm đang bán</div>
                @if($stats['low_stock'] > 0)
                <small style="color:var(--red);font-size:12px;"><i class="fas fa-exclamation-triangle me-1"></i>{{ $stats['low_stock'] }} sắp hết hàng</small>
                @else
                <small style="color:var(--green);font-size:12px;"><i class="fas fa-check-circle me-1"></i>Tồn kho ổn định</small>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ─── Charts ─── --}}
<div class="row g-4 mb-4">
    {{-- Revenue Chart --}}
    <div class="col-lg-8">
        <div class="admin-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h6 class="fw-700 mb-1">Doanh Thu 30 Ngày Gần Nhất</h6>
                    <small style="color:var(--text-muted)">
                        Tổng: <strong style="color:var(--gold)">{{ number_format(array_sum($chartRevenue), 0, ',', '.') }}đ</strong>
                    </small>
                </div>
                <div style="font-size:12px;color:var(--text-muted)">{{ now()->format('d/m/Y') }}</div>
            </div>
            <canvas id="revenueChart" height="90"></canvas>
        </div>
    </div>

    {{-- Order Status Doughnut --}}
    <div class="col-lg-4">
        <div class="admin-card">
            <h6 class="fw-700 mb-4">Trạng Thái Đơn Hàng</h6>
            <canvas id="statusChart" height="180"></canvas>
            <div class="mt-3">
                @php
                $statusColors = [
                    'pending' => '#F39C12', 'confirmed' => '#3498DB', 'processing' => '#9B59B6',
                    'shipping' => '#1ABC9C', 'delivered' => '#27AE60', 'cancelled' => '#E74C3C', 'refunded' => '#95A5A6',
                ];
                $statusLabels = [
                    'pending' => 'Chờ xác nhận', 'confirmed' => 'Đã xác nhận', 'processing' => 'Đang xử lý',
                    'shipping' => 'Đang giao', 'delivered' => 'Đã giao', 'cancelled' => 'Đã hủy', 'refunded' => 'Hoàn tiền',
                ];
                @endphp
                @foreach($orderStatusChart as $status => $count)
                <div class="d-flex align-items-center gap-2 mb-2">
                    <div style="width:10px;height:10px;border-radius:2px;background:{{ $statusColors[$status] ?? '#ccc' }};flex-shrink:0;"></div>
                    <span style="font-size:12px;flex:1;">{{ $statusLabels[$status] ?? $status }}</span>
                    <span style="font-size:12px;font-weight:600;">{{ $count }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Top Products --}}
    <div class="col-lg-5">
        <div class="admin-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h6 class="fw-700 mb-0">Sản Phẩm Bán Chạy</h6>
                <a href="{{ route('admin.reports.top-products') }}" style="font-size:12px;color:var(--gold);text-decoration:none;">Xem tất cả</a>
            </div>
            @foreach($topProducts as $i => $product)
            <div class="d-flex align-items-center gap-3 mb-4 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                <div style="font-size:18px;font-weight:700;color:{{ $i < 3 ? 'var(--gold)' : 'var(--text-muted)' }};width:24px;text-align:center;">
                    {{ $i + 1 }}
                </div>
                @if($product->primaryImage)
                <img src="{{ $product->primaryImage->url }}" alt="" style="width:44px;height:44px;border-radius:8px;object-fit:cover;">
                @else
                <div style="width:44px;height:44px;border-radius:8px;background:var(--body-bg);display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-box" style="color:var(--text-muted);font-size:16px;"></i>
                </div>
                @endif
                <div class="flex-1" style="min-width:0;">
                    <div style="font-size:13px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $product->name }}</div>
                    <div style="font-size:11px;color:var(--text-muted);">Đã bán: {{ number_format($product->sold_count) }}</div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:13px;font-weight:700;color:var(--gold);">{{ vnd($product->current_price) }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Recent Orders --}}
    <div class="col-lg-7">
        <div class="admin-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h6 class="fw-700 mb-0">Đơn Hàng Gần Đây</h6>
                <a href="{{ route('admin.orders.index') }}" style="font-size:12px;color:var(--gold);text-decoration:none;">Xem tất cả</a>
            </div>
            <div style="overflow-x:auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Mã ĐH</th>
                            <th>Khách hàng</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái</th>
                            <th>Ngày</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentOrders as $order)
                        <tr>
                            <td><strong style="font-size:12px;">{{ $order->order_code }}</strong></td>
                            <td>
                                <div style="font-size:13px;font-weight:500;">{{ $order->shipping_name }}</div>
                                <div style="font-size:11px;color:var(--text-muted);">{{ $order->shipping_phone }}</div>
                            </td>
                            <td style="font-weight:600;color:var(--gold);">{{ vnd($order->total) }}</td>
                            <td>
                                <span class="status-badge {{ $order->status }}">{{ $order->status_label }}</span>
                            </td>
                            <td style="font-size:12px;color:var(--text-muted);">{{ $order->created_at->format('d/m H:i') }}</td>
                            <td>
                                <a href="{{ route('admin.orders.show', $order) }}" class="btn-admin-secondary">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
// ─── Revenue Chart ─────────────────────────────────────────────
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
new Chart(revenueCtx, {
    type: 'bar',
    data: {
        labels: @json($chartDates),
        datasets: [{
            label: 'Doanh thu (đ)',
            data: @json($chartRevenue),
            backgroundColor: 'rgba(201, 168, 76, 0.2)',
            borderColor: '#C9A84C',
            borderWidth: 2,
            borderRadius: 6,
            borderSkipped: false,
        }, {
            label: 'Số đơn',
            data: @json($chartOrders),
            type: 'line',
            backgroundColor: 'rgba(52, 152, 219, 0.1)',
            borderColor: '#3498DB',
            borderWidth: 2,
            pointRadius: 3,
            pointBackgroundColor: '#3498DB',
            yAxisID: 'y1',
            tension: 0.4,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'top', labels: { font: { size: 12 }, boxWidth: 12 } },
            tooltip: {
                callbacks: {
                    label: (ctx) => {
                        if (ctx.datasetIndex === 0) {
                            return 'Doanh thu: ' + new Intl.NumberFormat('vi-VN').format(ctx.raw) + 'đ';
                        }
                        return 'Đơn hàng: ' + ctx.raw;
                    }
                }
            }
        },
        scales: {
            y: { grid: { color: 'rgba(0,0,0,.05)' }, ticks: { font: { size: 11 } } },
            y1: { position: 'right', grid: { display: false }, ticks: { font: { size: 11 } } },
            x: { grid: { display: false }, ticks: { font: { size: 11 } } }
        }
    }
});

// ─── Status Doughnut Chart ─────────────────────────────────────
const statusData = @json($orderStatusChart);
const statusColors = {
    pending: '#F39C12', confirmed: '#3498DB', processing: '#9B59B6',
    shipping: '#1ABC9C', delivered: '#27AE60', cancelled: '#E74C3C', refunded: '#95A5A6',
};
const statusLabels = {
    pending: 'Chờ XN', confirmed: 'Đã XN', processing: 'Xử lý',
    shipping: 'Đang giao', delivered: 'Đã giao', cancelled: 'Đã hủy', refunded: 'Hoàn tiền',
};

const statusCtx = document.getElementById('statusChart').getContext('2d');
new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: Object.keys(statusData).map(k => statusLabels[k] || k),
        datasets: [{
            data: Object.values(statusData),
            backgroundColor: Object.keys(statusData).map(k => statusColors[k] || '#ccc'),
            borderWidth: 0,
            hoverOffset: 8,
        }]
    },
    options: {
        cutout: '65%',
        plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: (ctx) => ` ${ctx.label}: ${ctx.raw} đơn` } }
        }
    }
});
</script>
@endsection
