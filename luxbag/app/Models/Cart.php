<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = ['user_id', 'session_id', 'product_id', 'variant_id', 'quantity'];

    public function product() { return $this->belongsTo(Product::class)->with(['primaryImage']); }
    public function variant() { return $this->belongsTo(ProductVariant::class); }
    public function user()    { return $this->belongsTo(User::class); }

    public function getSubtotalAttribute(): float
    {
        $price = $this->product->current_price;
        if ($this->variant && $this->variant->price_modifier) {
            $price += $this->variant->price_modifier;
        }
        return $price * $this->quantity;
    }

    public function getUnitPriceAttribute(): float
    {
        $price = $this->product->current_price;
        if ($this->variant && $this->variant->price_modifier) {
            $price += $this->variant->price_modifier;
        }
        return $price;
    }
}
