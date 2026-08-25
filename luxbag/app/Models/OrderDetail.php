<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'order_id', 'product_id', 'variant_id',
        'product_name', 'product_sku', 'variant_info', 'product_image',
        'quantity', 'unit_price', 'subtotal',
    ];

    protected $casts = ['unit_price' => 'decimal:2', 'subtotal' => 'decimal:2'];

    public function order()   { return $this->belongsTo(Order::class); }
    public function product() { return $this->belongsTo(Product::class); }
    public function variant() { return $this->belongsTo(ProductVariant::class); }

    public function getProductImageUrlAttribute(): string
    {
        return $this->product_image
            ? asset('storage/products/' . $this->product_image)
            : asset('assets/images/product-placeholder.jpg');
    }
}
