<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlashSale extends Model
{
    public $timestamps = false;
    protected $fillable = ['name', 'starts_at', 'ends_at', 'status', 'banner'];
    protected $casts = ['starts_at' => 'datetime', 'ends_at' => 'datetime'];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'flash_sale_products')
            ->withPivot(['discount_percent', 'sale_price', 'quantity_limit', 'sold_count']);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now());
    }

    public function isActive(): bool
    {
        return $this->status === 'active'
            && $this->starts_at <= now()
            && $this->ends_at >= now();
    }

    public function getSecondsRemainingAttribute(): int
    {
        return max(0, now()->diffInSeconds($this->ends_at, false));
    }
}
