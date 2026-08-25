<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code', 'type', 'value', 'min_order_amount', 'max_discount_amount',
        'max_uses', 'used_count', 'per_user_limit', 'applies_to', 'applies_to_id',
        'starts_at', 'expires_at', 'status', 'description',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
    ];

    public function usages()
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function isValid(): bool
    {
        if ($this->status !== 'active') return false;
        if ($this->expires_at && $this->expires_at->isPast()) return false;
        if ($this->starts_at && $this->starts_at->isFuture()) return false;
        if ($this->max_uses && $this->used_count >= $this->max_uses) return false;
        return true;
    }

    public function calculateDiscount(float $subtotal): float
    {
        if ($subtotal < $this->min_order_amount) return 0;

        if ($this->type === 'percent') {
            $discount = $subtotal * ($this->value / 100);
            if ($this->max_discount_amount) {
                $discount = min($discount, $this->max_discount_amount);
            }
            return $discount;
        }

        return min($this->value, $subtotal);
    }

    public function hasUserExceededLimit(int $userId): bool
    {
        if (!$this->per_user_limit) return false; // No limit set
        return $this->usages()->where('user_id', $userId)->count() >= $this->per_user_limit;
    }
}
