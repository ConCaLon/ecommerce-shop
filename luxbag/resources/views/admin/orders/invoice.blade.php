<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hóa Đơn #{{ $order->order_code }}</title>
    <style>
    * { box-sizing:border-box; margin:0; padding:0; }
    body { font-family: 'Arial', sans-serif; font-size:13px; color:#333; background:#fff; }
    .invoice-wrap { max-width:720px; margin:30px auto; padding:40px; }
    .header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:40px; border-bottom:2px solid #C9A84C; padding-bottom:24px; }
    .brand { font-size:28px; font-weight:700; color:#C9A84C; letter-spacing:2px; }
    .brand span { color:#333; }
    .invoice-num { font-size:20px; font-weight:700; color:#333; }
    .meta-grid { display:grid; grid-template-columns:1fr 1fr; gap:24px; margin-bottom:32px; }
    .meta-box { background:#f8f9fa; border-radius:8px; padding:16px; }
    .meta-box h6 { font-size:11px; text-transform:uppercase; letter-spacing:1.5px; color:#888; margin-bottom:10px; }
    .meta-box p { font-size:13px; line-height:1.8; }
    table { width:100%; border-collapse:collapse; margin-bottom:24px; }
    thead th { background:#C9A84C; color:#000; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:1px; padding:10px 12px; text-align:left; }
    tbody td { padding:12px; border-bottom:1px solid #eee; font-size:13px; vertical-align:middle; }
    tbody tr:last-child td { border-bottom:none; }
    .totals { max-width:280px; margin-left:auto; }
    .totals-row { display:flex; justify-content:space-between; padding:8px 0; font-size:13px; border-bottom:1px solid #eee; }
    .totals-row.grand { border-top:2px solid #C9A84C; border-bottom:none; font-weight:700; font-size:15px; color:#C9A84C; }
    .footer { margin-top:40px; padding-top:20px; border-top:1px solid #eee; text-align:center; font-size:12px; color:#888; }
    .status-pill { padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; }
    @media print {
        body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .no-print { display:none!important; }
        .invoice-wrap { margin:0; padding:24px; }
    }
    </style>
</head>
<body>

<div class="no-print" style="background:#f8f9fa;padding:12px;text-align:center;border-bottom:1px solid #dee2e6;">
    <button onclick="window.print()" style="background:#C9A84C;color:#000;border:none;padding:8px 24px;border-radius:8px;font-weight:700;cursor:pointer;margin-right:8px;">
        🖨 In Hóa Đơn
    </button>
    <a href="{{ route('admin.orders.show', $order) }}" style="color:#666;font-size:13px;">← Quay lại</a>
</div>

<div class="invoice-wrap">
    {{-- Header --}}
    <div class="header">
        <div>
            <div class="brand">Lux<span>Bag</span></div>
            <div style="font-size:12px;color:#888;margin-top:4px;">{{ setting('site_address', '123 Lê Lợi, Q.1, TP.HCM') }}</div>
            <div style="font-size:12px;color:#888;">{{ setting('site_phone', '+84 377277542') }}</div>
        </div>
        <div style="text-align:right;">
            <div class="invoice-num">HÓA ĐƠN</div>
            <div style="font-size:14px;font-weight:700;margin-top:4px;">#{{ $order->order_code }}</div>
            <div style="font-size:12px;color:#888;margin-top:4px;">Ngày: {{ $order->created_at->format('d/m/Y') }}</div>
            <span class="status-pill" style="background:{{ match($order->status) { 'delivered' => '#d4edda', 'cancelled' => '#f8d7da', default => '#fff3cd' } }};color:{{ match($order->status) { 'delivered' => '#155724', 'cancelled' => '#721c24', default => '#856404' } }}">
                {{ $order->status_label }}
            </span>
        </div>
    </div>

    {{-- Meta --}}
    <div class="meta-grid">
        <div class="meta-box">
            <h6>Khách Hàng</h6>
            <p>
                <strong>{{ $order->shipping_name }}</strong><br>
                {{ $order->shipping_phone }}<br>
                {{ $order->shipping_email }}
            </p>
        </div>
        <div class="meta-box">
            <h6>Địa Chỉ Giao Hàng</h6>
            <p>{{ $order->shipping_address }}</p>
        </div>
        <div class="meta-box">
            <h6>Thanh Toán</h6>
            <p>
                <strong>{{ $order->payment_method_label }}</strong><br>
                {{ match($order->payment_status) { 'paid' => '✓ Đã thanh toán', 'failed' => '✗ Thất bại', default => 'Chờ thanh toán' } }}
            </p>
        </div>
        <div class="meta-box">
            <h6>Vận Chuyển</h6>
            <p>
                {{ $order->shippingMethod?->name ?? 'Giao hàng tiêu chuẩn' }}<br>
                @if($order->shipping_fee > 0) {{ vnd($order->shipping_fee) }} @else Miễn phí @endif
            </p>
        </div>
    </div>

    {{-- Items Table --}}
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Sản phẩm</th>
                <th style="text-align:right;">Đơn giá</th>
                <th style="text-align:center;">SL</th>
                <th style="text-align:right;">Thành tiền</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->details as $i => $detail)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>
                    {{ $detail->product_name }}
                    @if($detail->variant_info)
                        <br><small style="color:#888;">{{ $detail->variant_info }}</small>
                    @endif
                </td>
                <td style="text-align:right;">{{ vnd($detail->unit_price) }}</td>
                <td style="text-align:center;">{{ $detail->quantity }}</td>
                <td style="text-align:right;font-weight:700;">{{ vnd($detail->subtotal) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Totals --}}
    <div class="totals">
        <div class="totals-row">
            <span>Tạm tính</span><span>{{ vnd($order->subtotal) }}</span>
        </div>
        <div class="totals-row">
            <span>Phí vận chuyển</span><span>{{ $order->shipping_fee > 0 ? vnd($order->shipping_fee) : 'Miễn phí' }}</span>
        </div>
        @if($order->discount_amount > 0)
        <div class="totals-row">
            <span>Giảm giá ({{ $order->coupon_code }})</span>
            <span style="color:#27ae60;">-{{ vnd($order->discount_amount) }}</span>
        </div>
        @endif
        <div class="totals-row grand">
            <span>TỔNG CỘNG</span><span>{{ vnd($order->total) }}</span>
        </div>
    </div>

    {{-- Notes --}}
    @if($order->notes)
    <div style="margin-top:24px;padding:16px;background:#f8f9fa;border-radius:8px;font-size:13px;">
        <strong>Ghi chú:</strong> {{ $order->notes }}
    </div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        <p>Cảm ơn quý khách đã tin tưởng và mua hàng tại <strong>Bloom & Bold</strong>!</p>
        <p>Mọi thắc mắc vui lòng liên hệ: {{ setting('site_phone', '+84 377277542') }} | {{ setting('site_email', 'contact@bloomandbold.vn') }}</p>
    </div>
</div>

</body>
</html>
