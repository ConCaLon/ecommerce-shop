@extends('layouts.admin')

@section('title', 'Đơn Hàng #'.$order->order_code)
@section('page-title', 'Chi Tiết Đơn Hàng')
@section('breadcrumb', 'Đơn Hàng / #'.$order->order_code)

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-700 mb-1">Đơn Hàng #{{ $order->order_code }}</h5>
        <small style="color:var(--text-muted);">Đặt lúc {{ $order->created_at->format('H:i, d/m/Y') }}</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.orders.invoice', $order) }}" target="_blank" class="btn-admin-secondary">
            <i class="fas fa-print me-1"></i>In hóa đơn
        </a>
        <a href="{{ route('admin.orders.index') }}" class="btn-admin-secondary">
            <i class="fas fa-arrow-left me-1"></i>Quay lại
        </a>
    </div>
</div>

<div class="row g-4">
    {{-- ─── Order Items + Timeline ─── --}}
    <div class="col-lg-8">
        {{-- Products --}}
        <div class="admin-card mb-4">
            <h6 class="fw-700 mb-4">Sản Phẩm Đặt</h6>
            @foreach($order->details as $detail)
            <div class="d-flex align-items-center gap-3 {{ !$loop->last ? 'mb-4 pb-4 border-bottom' : '' }}">
                @if($detail->product_image)
                    <img src="{{ asset('storage/products/'.$detail->product_image) }}" alt=""
                         style="width:60px;height:60px;border-radius:8px;object-fit:cover;border:1px solid var(--border);">
                @else
                    <div style="width:60px;height:60px;border-radius:8px;background:var(--body-bg);display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-box" style="color:var(--text-muted);"></i>
                    </div>
                @endif
                <div class="flex-1">
                    <div style="font-weight:600;font-size:13.5px;">{{ $detail->product_name }}</div>
                    @if($detail->variant_info)
                        <div style="font-size:12px;color:var(--text-muted);">{{ $detail->variant_info }}</div>
                    @endif
                    <div style="font-size:12px;color:var(--text-muted);">SKU: {{ $detail->product_sku }}</div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:13px;color:var(--text-muted);">{{ vnd($detail->unit_price) }} × {{ $detail->quantity }}</div>
                    <div style="font-weight:700;color:var(--gold);">{{ vnd($detail->subtotal) }}</div>
                </div>
            </div>
            @endforeach

            <div class="border-top pt-3 mt-3">
                <div class="d-flex justify-content-between mb-2">
                    <span style="font-size:13px;color:var(--text-muted);">Tạm tính</span>
                    <span style="font-size:13px;">{{ vnd($order->subtotal) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span style="font-size:13px;color:var(--text-muted);">Phí vận chuyển</span>
                    <span style="font-size:13px;">{{ $order->shipping_fee > 0 ? vnd($order->shipping_fee) : 'Miễn phí' }}</span>
                </div>
                @if($order->discount_amount > 0)
                <div class="d-flex justify-content-between mb-2">
                    <span style="font-size:13px;color:var(--text-muted);">Giảm giá ({{ $order->coupon_code }})</span>
                    <span style="font-size:13px;color:var(--green);">-{{ vnd($order->discount_amount) }}</span>
                </div>
                @endif
                <div class="d-flex justify-content-between border-top pt-2 mt-2">
                    <span style="font-weight:700;font-size:15px;">Tổng cộng</span>
                    <span style="font-weight:700;font-size:15px;color:var(--gold);">{{ vnd($order->total) }}</span>
                </div>
            </div>
        </div>

        {{-- Update Status --}}
        <div class="admin-card">
            <h6 class="fw-700 mb-4">Cập Nhật Trạng Thái</h6>
            <div class="d-flex flex-wrap gap-2">
                @php
                $nextStatuses = [
                    'pending' => ['confirmed', 'cancelled'],
                    'confirmed' => ['processing', 'cancelled'],
                    'processing' => ['shipping', 'cancelled'],
                    'shipping' => ['delivered'],
                    'delivered' => ['refunded'],
                ];
                $available = $nextStatuses[$order->status] ?? [];
                $statusLabels = ['confirmed'=>'Xác nhận','processing'=>'Đang xử lý','shipping'=>'Đang giao','delivered'=>'Đã giao','cancelled'=>'Hủy đơn','refunded'=>'Hoàn tiền'];
                $statusColors = ['confirmed'=>'blue','processing'=>'blue','shipping'=>'blue','delivered'=>'green','cancelled'=>'red','refunded'=>'orange'];
                @endphp

                @foreach($available as $s)
                <button type="button" class="btn-admin-{{ $s === 'cancelled' ? 'danger' : 'primary' }}"
                    onclick="updateStatus('{{ $s }}')">
                    <i class="fas {{ match($s) { 'confirmed'=>'fa-check','processing'=>'fa-cog','shipping'=>'fa-truck','delivered'=>'fa-box-open','cancelled'=>'fa-times','refunded'=>'fa-undo', default=>'fa-arrow-right' } }} me-1"></i>
                    {{ $statusLabels[$s] ?? $s }}
                </button>
                @endforeach
            </div>

            @if($order->cancelled_reason)
            <div class="alert alert-danger mt-3 mb-0">
                <strong>Lý do hủy:</strong> {{ $order->cancelled_reason }}
            </div>
            @endif

            @if($order->notes)
            <div class="mt-3 p-3" style="background:var(--body-bg);border-radius:8px;font-size:13px;">
                <strong>Ghi chú khách:</strong> {{ $order->notes }}
            </div>
            @endif
        </div>
    </div>

    {{-- ─── Sidebar Info ─── --}}
    <div class="col-lg-4">
        {{-- Status --}}
        <div class="admin-card mb-4">
            <h6 class="fw-700 mb-3">Trạng Thái</h6>
            <div class="text-center mb-3">
                <span class="status-badge {{ $order->status }}" style="font-size:14px;padding:8px 20px;">
                    {{ $order->status_label }}
                </span>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span style="font-size:13px;color:var(--text-muted);">Thanh toán</span>
                <span style="font-size:13px;font-weight:600;">{{ $order->payment_method_label }}</span>
            </div>
            <div class="d-flex justify-content-between">
                <span style="font-size:13px;color:var(--text-muted);">TT thanh toán</span>
                <span style="font-size:13px;font-weight:600;color:{{ $order->payment_status === 'paid' ? 'var(--green)' : 'var(--orange)' }}">
                    {{ match($order->payment_status) { 'paid' => 'Đã thanh toán', 'failed' => 'Thất bại', default => 'Chưa thanh toán' } }}
                </span>
            </div>
        </div>

        {{-- Customer --}}
        <div class="admin-card mb-4">
            <h6 class="fw-700 mb-3">Khách Hàng</h6>
            @if($order->user)
            <div class="d-flex align-items-center gap-3 mb-3">
                <img src="{{ $order->user->avatar_url }}" alt=""
                     style="width:40px;height:40px;border-radius:50%;border:2px solid var(--gold);">
                <div>
                    <div style="font-weight:600;font-size:13.5px;">{{ $order->user->name }}</div>
                    <div style="font-size:12px;color:var(--text-muted);">{{ $order->user->email }}</div>
                </div>
            </div>
            <a href="{{ route('admin.customers.show', $order->user) }}" class="btn-admin-secondary w-100">
                <i class="fas fa-user me-1"></i>Xem hồ sơ
            </a>
            @else
            <p style="color:var(--text-muted);font-size:13px;">Khách vãng lai</p>
            @endif
        </div>

        {{-- Shipping --}}
        <div class="admin-card mb-4">
            <h6 class="fw-700 mb-3">Địa Chỉ Giao Hàng</h6>
            <div style="font-size:13.5px;line-height:1.8;">
                <div style="font-weight:600;">{{ $order->shipping_name }}</div>
                <div style="color:var(--text-muted);">{{ $order->shipping_phone }}</div>
                <div style="color:var(--text-muted);">{{ $order->shipping_email }}</div>
                <div style="margin-top:8px;">{{ $order->shipping_address }}</div>
            </div>
            @if($order->shippingMethod)
            <div class="mt-3 pt-3 border-top">
                <span style="font-size:12px;color:var(--text-muted);">Vận chuyển: </span>
                <span style="font-size:12px;font-weight:600;">{{ $order->shippingMethod->name }}</span>
            </div>
            @endif
        </div>

        {{-- Timeline --}}
        <div class="admin-card">
            <h6 class="fw-700 mb-3">Lịch Sử</h6>
            <div style="position:relative;padding-left:20px;">
                @if($order->created_at)
                <div class="timeline-item" style="margin-bottom:16px;">
                    <div style="width:10px;height:10px;border-radius:50%;background:var(--gold);position:absolute;left:0;top:4px;"></div>
                    <div style="font-size:12px;font-weight:600;">Đặt hàng</div>
                    <div style="font-size:11px;color:var(--text-muted);">{{ $order->created_at->format('H:i d/m/Y') }}</div>
                </div>
                @endif
                @if($order->confirmed_at)
                <div style="margin-bottom:16px;">
                    <div style="font-size:12px;font-weight:600;">Đã xác nhận</div>
                    <div style="font-size:11px;color:var(--text-muted);">{{ $order->confirmed_at->format('H:i d/m/Y') }}</div>
                </div>
                @endif
                @if($order->shipped_at)
                <div style="margin-bottom:16px;">
                    <div style="font-size:12px;font-weight:600;">Đang giao hàng</div>
                    <div style="font-size:11px;color:var(--text-muted);">{{ $order->shipped_at->format('H:i d/m/Y') }}</div>
                </div>
                @endif
                @if($order->delivered_at)
                <div style="margin-bottom:16px;">
                    <div style="font-size:12px;font-weight:600;color:var(--green);">Đã giao hàng ✓</div>
                    <div style="font-size:11px;color:var(--text-muted);">{{ $order->delivered_at->format('H:i d/m/Y') }}</div>
                </div>
                @endif
                @if($order->cancelled_at)
                <div>
                    <div style="font-size:12px;font-weight:600;color:var(--red);">Đã hủy</div>
                    <div style="font-size:11px;color:var(--text-muted);">{{ $order->cancelled_at->format('H:i d/m/Y') }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Cancel Modal --}}
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:12px;">
            <div class="modal-header" style="border-bottom:1px solid var(--border);">
                <h6 class="modal-title fw-700">Hủy Đơn Hàng</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3 admin-form">
                    <label class="form-label">Lý do hủy</label>
                    <textarea id="cancelNote" class="form-control" rows="3" placeholder="Nhập lý do hủy đơn..."></textarea>
                </div>
            </div>
            <div class="modal-footer" style="border-top:1px solid var(--border);">
                <button type="button" class="btn-admin-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn-admin-danger" onclick="confirmCancel()"><i class="fas fa-times me-1"></i>Hủy đơn</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let pendingStatus = null;

function updateStatus(status) {
    if (status === 'cancelled') {
        pendingStatus = 'cancelled';
        new bootstrap.Modal(document.getElementById('cancelModal')).show();
        return;
    }
    doUpdateStatus(status, '');
}

function confirmCancel() {
    const note = document.getElementById('cancelNote').value;
    doUpdateStatus('cancelled', note);
}

function doUpdateStatus(status, note) {
    $.ajax({
        url: '{{ route('admin.orders.status', $order) }}',
        method: 'PATCH',
        data: { status, note },
    }).done(res => {
        if (res.success) location.reload();
    }).fail(() => alert('Cập nhật thất bại!'));
}
</script>
@endsection
