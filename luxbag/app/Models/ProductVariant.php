<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'product_id', 'size', 'color', 'color_hex',
        'sku', 'stock', 'price_modifier', 'image', 'status',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getLabelAttribute(): string
    {
        $parts = [];
        if ($this->size) $parts[] = 'Size: ' . $this->size;
        if ($this->color) $parts[] = 'Màu: ' . $this->color;
        return implode(', ', $parts) ?: 'Default';
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/products/' . $this->image) : null;
    }
}
