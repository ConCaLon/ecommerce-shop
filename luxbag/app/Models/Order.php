<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id', 'order_code', 'status', 'payment_method', 'payment_status',
        'shipping_method_id', 'shipping_name', 'shipping_phone', 'shipping_email',
        'shipping_address', 'shipping_province', 'shipping_district', 'shipping_ward',
        'coupon_id', 'coupon_code', 'subtotal', 'shipping_fee', 'discount_amount', 'total',
        'notes', 'cancelled_reason', 'confirmed_at', 'shipped_at', 'delivered_at', 'cancelled_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'confirmed_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    // ─── Status constants ─────────────────────────────────────────
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SHIPPING = 'shipping';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_REFUNDED = 'refunded';

    const STATUS_LABELS = [
        'pending' => ['label' => 'Chờ xác nhận', 'color' => 'warning'],
        'confirmed' => ['label' => 'Đã xác nhận', 'color' => 'info'],
        'processing' => ['label' => 'Đang xử lý', 'color' => 'primary'],
        'shipping' => ['label' => 'Đang giao hàng', 'color' => 'info'],
        'delivered' => ['label' => 'Đã giao hàng', 'color' => 'success'],
        'cancelled' => ['label' => 'Đã hủy', 'color' => 'danger'],
        'refunded' => ['label' => 'Đã hoàn tiền', 'color' => 'secondary'],
    ];

    const PAYMENT_STATUS_LABELS = [
        'pending' => ['label' => 'Chờ thanh toán', 'color' => 'warning'],
        'paid' => ['label' => 'Đã thanh toán', 'color' => 'success'],
        'failed' => ['label' => 'Thất bại', 'color' => 'danger'],
        'refunded' => ['label' => 'Đã hoàn tiền', 'color' => 'secondary'],
    ];

    // ─── Relationships ────────────────────────────────────────────
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class)->latest();
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function shippingMethod()
    {
        return $this->belongsTo(ShippingMethod::class);
    }

    // ─── Helpers ──────────────────────────────────────────────────
    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status]['label'] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUS_LABELS[$this->status]['color'] ?? 'secondary';
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match($this->payment_method) {
            'cod' => 'Thanh toán khi nhận hàng',
            'bank_transfer' => 'Chuyển khoản ngân hàng',
            'vnpay' => 'VNPay',
            'momo' => 'Ví MoMo',
            default => $this->payment_method,
        };
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }

    public function canBeReviewed(): bool
    {
        return $this->status === 'delivered';
    }

    public static function generateCode(): string
    {
        do {
            $code = 'LB' . date('Ymd') . strtoupper(substr(uniqid(), -4));
        } while (self::where('order_code', $code)->exists());
        return $code;
    }
}
