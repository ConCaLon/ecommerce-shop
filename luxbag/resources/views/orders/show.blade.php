@extends('layouts.app')

@section('title', 'Đơn Hàng #'.$order->order_code)

@section('content')
<div class="container py-5">
    <nav style="font-size:13px;color:#888;margin-bottom:24px;">
        <a href="{{ route('orders.history') }}" style="color:#888;text-decoration:none;">Đơn hàng của tôi</a>
        <i class="fas fa-chevron-right mx-2" style="font-size:10px;"></i>
        <span style="color:#1a1d24;font-weight:600;">#{{ $order->order_code }}</span>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 fw-700 mb-0">Chi Tiết Đơn Hàng</h1>
        <div class="d-flex gap-2">
            @if($order->canBeCancelled())
            <button type="button" onclick="showCancelModal()"
                style="padding:9px 20px;background:#fff;border:1.5px solid #E74C3C;border-radius:10px;font-weight:600;font-size:13px;cursor:pointer;color:#E74C3C;">
                <i class="fas fa-times me-1"></i>Hủy Đơn
            </button>
            @endif
        </div>
    </div>

    <div class="row g-4">
        {{-- ─── Main ─── --}}
        <div class="col-lg-8">
            {{-- Status Timeline --}}
            <div style="background:#fff;border-radius:16px;box-shadow:0 2px 20px rgba(0,0,0,.06);padding:28px;margin-bottom:20px;">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-700 mb-0">Trạng Thái Đơn Hàng</h5>
                    <span style="padding:6px 16px;border-radius:20px;font-size:13px;font-weight:700;
                        {{ match($order->status) {
                            'delivered' => 'background:rgba(39,174,96,.1);color:#27AE60;',
                            'cancelled' => 'background:rgba(231,76,60,.1);color:#E74C3C;',
                            'shipping' => 'background:rgba(52,152,219,.1);color:#3498DB;',
                            default => 'background:rgba(243,156,18,.1);color:#F39C12;'
                        } }}">
                        {{ $order->status_label }}
                    </span>
                </div>

                {{-- Progress Steps --}}
                @php
                $steps = [
                    ['key' => 'pending', 'icon' => 'fa-clock', 'label' => 'Chờ xác nhận', 'time' => $order->created_at],
                    ['key' => 'confirmed', 'icon' => 'fa-check-circle', 'label' => 'Đã xác nhận', 'time' => $order->confirmed_at],
                    ['key' => 'shipping', 'icon' => 'fa-truck', 'label' => 'Đang giao hàng', 'time' => $order->shipped_at],
                    ['key' => 'delivered', 'icon' => 'fa-box-open', 'label' => 'Đã giao hàng', 'time' => $order->delivered_at],
                ];
                $statusOrder = ['pending', 'confirmed', 'processing', 'shipping', 'delivered'];
                $currentIndex = array_search($order->status, $statusOrder);
                @endphp

                @if($order->status !== 'cancelled')
                <div style="display:flex;gap:0;margin-bottom:8px;">
                    @foreach($steps as $i => $step)
                    @php $isDone = in_array($step['key'], array_slice($statusOrder, 0, $currentIndex + 1)); @endphp
                    <div style="flex:1;text-align:center;{{ !$loop->last ? 'padding-right:0;' : '' }}">
                        <div style="position:relative;display:flex;align-items:center;justify-content:center;">
                            @if(!$loop->last)
                            <div style="position:absolute;left:50%;top:50%;transform:translateY(-50%);width:100%;height:2px;background:{{ $isDone ? '#8A6FD6' : '#e8e8e8' }};z-index:0;"></div>
                            @endif
                            <div style="width:40px;height:40px;border-radius:50%;background:{{ $isDone ? 'linear-gradient(135deg,#8A6FD6,#6345B5)' : '#f0f0f0' }};display:flex;align-items:center;justify-content:center;position:relative;z-index:1;flex-shrink:0;">
                                <i class="fas {{ $step['icon'] }}" style="color:{{ $isDone ? '#fff' : '#bbb' }};font-size:14px;"></i>
                            </div>
                        </div>
                        <div style="font-size:11px;font-weight:{{ $isDone ? '700' : '500' }};color:{{ $isDone ? '#2D243D' : '#aaa' }};margin-top:8px;">{{ $step['label'] }}</div>
                        @if($step['time'])
                        <div style="font-size:10px;color:#888;">{{ $step['time']->format('d/m H:i') }}</div>
                        @endif
                    </div>
                    @endforeach
                </div>
                @else
                <div style="background:rgba(231,76,60,.06);border:1px solid rgba(231,76,60,.15);border-radius:10px;padding:14px 18px;">
                    <div style="color:#E74C3C;font-weight:700;"><i class="fas fa-times-circle me-2"></i>Đơn hàng đã bị hủy</div>
                    @if($order->cancelled_reason)
                    <div style="font-size:13px;color:#888;margin-top:4px;">Lý do: {{ $order->cancelled_reason }}</div>
                    @endif
                    @if($order->cancelled_at)
                    <div style="font-size:12px;color:#aaa;margin-top:2px;">{{ $order->cancelled_at->format('H:i d/m/Y') }}</div>
                    @endif
                </div>
                @endif
            </div>

            {{-- Products --}}
            <div style="background:#fff;border-radius:16px;box-shadow:0 2px 20px rgba(123,94,201,.06);border:1px solid #EAE1F8;padding:28px;">
                <h5 class="fw-700 mb-4">Sản Phẩm ({{ $order->details->count() }})</h5>
                @foreach($order->details as $detail)
                <div style="display:flex;gap:14px;align-items:center;padding:12px 0;{{ !$loop->last ? 'border-bottom:1px solid #F3EDFB;' : '' }}">
                    @if($detail->product?->primaryImage)
                        <img src="{{ $detail->product->primaryImage->url }}" alt=""
                             style="width:60px;height:60px;border-radius:10px;object-fit:cover;border:1px solid #eee;">
                    @else
                        <div style="width:60px;height:60px;border-radius:10px;background:#f5f5f5;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i class="fas fa-box" style="color:#ccc;font-size:20px;"></i>
                        </div>
                    @endif
                    <div class="flex-1">
                        <div style="font-weight:700;font-size:14px;">{{ $detail->product_name }}</div>
                        @if($detail->variant_info)<div style="font-size:12px;color:#888;margin-top:2px;">{{ $detail->variant_info }}</div>@endif
                        <div style="font-size:12px;color:#aaa;margin-top:2px;">{{ vnd($detail->unit_price) }} × {{ $detail->quantity }}</div>
                    </div>
                    <div style="font-weight:700;font-size:15px;text-align:right;">{{ vnd($detail->subtotal) }}</div>
                </div>
                @endforeach

                <div style="margin-top:20px;padding-top:20px;border-top:1px solid #F3EDFB;">
                    <div class="d-flex justify-content-between mb-2"><span style="color:#888;">Tạm tính</span><span>{{ vnd($order->subtotal) }}</span></div>
                    <div class="d-flex justify-content-between mb-2"><span style="color:#888;">Phí vận chuyển</span><span>{{ $order->shipping_fee > 0 ? vnd($order->shipping_fee) : 'Miễn phí' }}</span></div>
                    @if($order->discount_amount > 0)
                    <div class="d-flex justify-content-between mb-2"><span style="color:#888;">Giảm giá ({{ $order->coupon_code }})</span><span style="color:#27AE60;">-{{ vnd($order->discount_amount) }}</span></div>
                    @endif
                    <div class="d-flex justify-content-between" style="border-top:2px solid #F3EDFB;padding-top:12px;margin-top:8px;">
                        <strong style="font-size:16px;">Tổng cộng</strong>
                        <strong style="font-size:20px;color:#6345B5;">{{ vnd($order->total) }}</strong>
                    </div>
                </div>
            </div>
        </div>

        {{-- ─── Sidebar ─── --}}
        <div class="col-lg-4">
            {{-- Shipping --}}
            <div style="background:#fff;border-radius:16px;box-shadow:0 2px 20px rgba(123,94,201,.06);border:1px solid #EAE1F8;padding:24px;margin-bottom:16px;">
                <h6 style="font-weight:700;margin-bottom:16px;">Địa Chỉ Giao Hàng</h6>
                <div style="font-weight:700;font-size:14px;">{{ $order->shipping_name }}</div>
                <div style="font-size:13px;color:#888;margin-top:2px;">{{ $order->shipping_phone }}</div>
                <div style="font-size:13px;color:#666;margin-top:8px;line-height:1.7;">{{ $order->shipping_address }}</div>
            </div>

            {{-- Payment --}}
            <div style="background:#fff;border-radius:16px;box-shadow:0 2px 20px rgba(123,94,201,.06);border:1px solid #EAE1F8;padding:24px;margin-bottom:16px;">
                <h6 style="font-weight:700;margin-bottom:16px;">Thanh Toán</h6>
                <div style="font-size:14px;font-weight:600;">{{ $order->payment_method_label }}</div>
                <div style="margin-top:8px;margin-bottom:12px;">
                    <span style="font-size:12px;padding:3px 10px;border-radius:20px;font-weight:700;
                        {{ $order->payment_status === 'paid' ? 'background:rgba(39,174,96,.1);color:#27AE60;' : 'background:rgba(243,156,18,.1);color:#F39C12;' }}">
                        {{ match($order->payment_status) { 'paid' => 'Đã thanh toán', 'failed' => 'Thất bại', default => 'Chờ thanh toán' } }}
                    </span>
                </div>

                @if($order->payment_method === 'bank_transfer' && $order->payment_status !== 'paid' && $order->status !== 'cancelled')
                <div style="background:#F7F2FD;border:1px solid #EAE0FA;border-radius:10px;padding:14px;font-size:12.5px;margin-top:14px;">
                    <div style="font-weight:700;margin-bottom:6px;color:#6345B5;"><i class="fas fa-university me-1"></i>Thông tin chuyển khoản:</div>
                    <div style="margin-bottom:3px;">Ngân hàng: <strong>Techcombank</strong></div>
                    <div style="margin-bottom:3px;">STK: <strong>1010 1812 2005</strong></div>
                    <div style="margin-bottom:3px;">Chủ tài khoản: <strong>AN CONG CHUAN</strong></div>
                    <div style="margin-bottom:3px;">Số tiền: <strong style="color:#6345B5;">{{ vnd($order->total) }}</strong></div>
                    <div style="margin-bottom:8px;">Nội dung: <strong style="color:#e74c3c;">{{ $order->order_code }}</strong></div>
                    
                    <div style="text-align:center;margin:12px 0 6px;">
                        <img src="{{ asset('assets/images/techcombank_qr.png') }}" alt="Techcombank QR Code" style="width:120px;height:120px;object-fit:contain;border:2px solid #fff;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,0.05);">
                    </div>
                    <div style="font-size:11px;color:#777;line-height:1.4;text-align:center;">
                        Quét mã QR và nhập đúng mã đơn hàng làm nội dung chuyển khoản.
                    </div>
                </div>
                @endif
            </div>

            {{-- Notes --}}
            @if($order->notes)
            <div style="background:#fff;border-radius:16px;box-shadow:0 2px 20px rgba(0,0,0,.06);padding:24px;">
                <h6 style="font-weight:700;margin-bottom:12px;">Ghi Chú</h6>
                <p style="font-size:13px;color:#666;margin-bottom:0;">{{ $order->notes }}</p>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Cancel Modal --}}
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:16px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Hủy Đơn Hàng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('orders.cancel', $order->order_code) }}">
                @csrf @method('DELETE')
                <div class="modal-body">
                    <p style="color:#666;font-size:14px;">Bạn có chắc muốn hủy đơn hàng <strong>#{{ $order->order_code }}</strong>?</p>
                    <textarea name="reason" class="form-control" rows="3" placeholder="Lý do hủy (tùy chọn)..."></textarea>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-danger fw-bold"><i class="fas fa-times me-1"></i>Xác Nhận Hủy</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function showCancelModal() {
    new bootstrap.Modal(document.getElementById('cancelModal')).show();
}
</script>
@endsection
