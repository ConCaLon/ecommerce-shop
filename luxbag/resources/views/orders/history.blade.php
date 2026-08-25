@extends('layouts.app')

@section('title', 'Lịch Sử Đơn Hàng')
@section('meta_description', 'Xem lịch sử các đơn hàng của bạn tại Bloom & Bold.')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 fw-700 mb-0">📦 Đơn Hàng Của Tôi</h1>
    </div>

    @if($orders->isEmpty())
    <div style="text-align:center;padding:80px;background:#fff;border-radius:16px;box-shadow:0 2px 20px rgba(123,94,201,.06);border:1px solid #EAE1F8;">
        <div style="font-size:64px;margin-bottom:20px;opacity:.4;">📦</div>
        <h5 style="color:#555;margin-bottom:12px;">Bạn chưa có đơn hàng nào</h5>
        <a href="{{ route('products.index') }}" style="display:inline-block;background:linear-gradient(135deg,#8A6FD6,#6345B5);color:#fff;padding:12px 28px;border-radius:12px;font-weight:700;text-decoration:none;box-shadow:0 4px 20px rgba(138,111,214,.35);">
            Mua Sắm Ngay
        </a>
    </div>
    @else

    {{-- Status Filter --}}
    <div class="d-flex flex-wrap gap-2 mb-4">
        @foreach(['' => 'Tất cả', 'pending' => 'Chờ xác nhận', 'confirmed' => 'Đã xác nhận', 'shipping' => 'Đang giao', 'delivered' => 'Đã giao', 'cancelled' => 'Đã hủy'] as $key => $label)
        <a href="{{ route('orders.history') }}{{ $key ? '?status='.$key : '' }}"
           style="padding:7px 16px;border-radius:20px;font-size:13px;font-weight:600;text-decoration:none;
                  {{ request('status') == $key && ($key || !request('status'))
                    ? 'background:linear-gradient(135deg,#8A6FD6,#6345B5);color:#fff;'
                    : 'background:#fff;color:#666;border:1px solid #EAE1F8;' }}">
            {{ $label }}
        </a>
        @endforeach
    </div>

    {{-- Orders List --}}
    @foreach($orders as $order)
    <div style="background:#fff;border-radius:16px;box-shadow:0 2px 20px rgba(123,94,201,.06);border:1px solid #EAE1F8;padding:24px;margin-bottom:16px;">
        {{-- Header --}}
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3 pb-3" style="border-bottom:1px solid #F3EDFB;">
            <div>
                <div style="font-size:12px;color:#888;margin-bottom:4px;">{{ $order->created_at->format('d/m/Y H:i') }}</div>
                <span style="font-family:monospace;font-weight:700;font-size:15px;color:#6345B5;">{{ $order->order_code }}</span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span style="padding:5px 14px;border-radius:20px;font-size:12px;font-weight:700;
                    {{ match($order->status) {
                        'delivered' => 'background:rgba(39,174,96,.1);color:#27AE60;',
                        'cancelled' => 'background:rgba(231,76,60,.1);color:#E74C3C;',
                        'shipping' => 'background:rgba(52,152,219,.1);color:#3498DB;',
                        default => 'background:rgba(243,156,18,.1);color:#F39C12;'
                    } }}">
                    {{ $order->status_label }}
                </span>
                <strong style="font-size:15px;color:#6345B5;">{{ vnd($order->total) }}</strong>
            </div>
        </div>

        {{-- Products Preview --}}
        <div class="d-flex flex-wrap gap-3 mb-3">
            @foreach($order->details->take(3) as $detail)
            <div class="d-flex align-items-center gap-2" style="font-size:13px;">
                @if($detail->product?->primaryImage)
                    <img src="{{ $detail->product->primaryImage->url }}" alt=""
                         style="width:40px;height:40px;border-radius:6px;object-fit:cover;border:1px solid #eee;">
                @endif
                <div>
                    <div style="font-weight:600;max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $detail->product_name }}</div>
                    <div style="color:#888;font-size:12px;">x{{ $detail->quantity }}</div>
                </div>
            </div>
            @endforeach
            @if($order->details->count() > 3)
            <div style="font-size:13px;color:#888;align-self:center;">+{{ $order->details->count() - 3 }} sản phẩm khác</div>
            @endif
        </div>

        {{-- Actions --}}
        <div class="d-flex gap-2">
            <a href="{{ route('orders.show', $order->order_code) }}"
               style="padding:9px 20px;background:#f8f8f8;border:1px solid #e8e8e8;border-radius:10px;font-weight:600;font-size:13px;text-decoration:none;color:#333;">
                <i class="fas fa-eye me-1"></i>Xem chi tiết
            </a>
            @if($order->canBeCancelled())
            <button type="button" onclick="showCancelForm('{{ $order->order_code }}')"
                style="padding:9px 20px;background:#fff;border:1px solid #E74C3C;border-radius:10px;font-weight:600;font-size:13px;cursor:pointer;color:#E74C3C;">
                <i class="fas fa-times me-1"></i>Hủy đơn
            </button>
            @endif
        </div>
    </div>
    @endforeach

    {{-- Pagination --}}
    @if($orders->hasPages())
    <div class="d-flex justify-content-center mt-4">{{ $orders->links() }}</div>
    @endif

    @endif
</div>

{{-- Cancel Modal --}}
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:16px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Hủy Đơn Hàng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="cancelForm" method="POST">
                @csrf
                <div class="modal-body">
                    <p style="color:#666;font-size:14px;">Lý do hủy (tùy chọn):</p>
                    <textarea name="reason" class="form-control" rows="3" placeholder="Nhập lý do hủy đơn..."></textarea>
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
function showCancelForm(orderCode) {
    document.getElementById('cancelForm').action = `/don-hang/${orderCode}/huy`;
    new bootstrap.Modal(document.getElementById('cancelModal')).show();
}
</script>
@endsection
