<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'category_id', 'brand_id', 'name', 'slug', 'sku',
        'description', 'short_description', 'price', 'sale_price',
        'stock', 'weight', 'material', 'status', 'is_featured',
        'is_new', 'sold_count', 'view_count', 'avg_rating', 'review_count',
        'meta_title', 'meta_description',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'is_featured' => 'boolean',
        'is_new' => 'boolean',
        'avg_rating' => 'decimal:2',
    ];

    // ─── Boot ─────────────────────────────────────────────────────
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
        });
    }

    // ─── Relationships ────────────────────────────────────────────
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class)->where('status', 'active');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class)->where('status', 'approved');
    }

    public function wishlistedBy()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function flashSales()
    {
        return $this->belongsToMany(FlashSale::class, 'flash_sale_products')
            ->withPivot(['discount_percent', 'sale_price', 'quantity_limit', 'sold_count']);
    }

    // ─── Scopes ───────────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeNew($query)
    {
        return $query->where('is_new', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    public function scopeSearch($query, string $term)
    {
        // Use fulltext search on MySQL; fall back to LIKE for SQLite (tests)
        if (\DB::connection()->getDriverName() === 'sqlite') {
            return $query->where('name', 'LIKE', "%{$term}%")
                ->orWhere('description', 'LIKE', "%{$term}%");
        }

        return $query->whereFullText(['name', 'description'], $term)
            ->orWhere('name', 'LIKE', "%{$term}%");
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByBrand($query, $brandId)
    {
        return $query->where('brand_id', $brandId);
    }

    public function scopePriceBetween($query, $min, $max)
    {
        if ($min) $query->where('price', '>=', $min);
        if ($max) $query->where('price', '<=', $max);
        return $query;
    }

    // ─── Helpers / Accessors ──────────────────────────────────────
    public function getCurrentPriceAttribute(): float
    {
        return ($this->sale_price && $this->sale_price > 0) ? $this->sale_price : $this->price;
    }

    public function getDiscountPercentAttribute(): int
    {
        if ($this->sale_price && $this->sale_price > 0 && $this->price > 0) {
            return (int) round((1 - $this->sale_price / $this->price) * 100);
        }
        return 0;
    }

    public function getIsOnSaleAttribute(): bool
    {
        return $this->sale_price !== null && $this->sale_price > 0 && $this->sale_price < $this->price;
    }

    public function getThumbnailAttribute(): string
    {
        $primary = $this->primaryImage;
        if ($primary) {
            return asset('storage/products/' . $primary->image_path);
        }
        $first = $this->images->first();
        if ($first) {
            return asset('storage/products/' . $first->image_path);
        }
        return asset('assets/images/product-placeholder.jpg');
    }

    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->current_price, 0, ',', '.') . 'đ';
    }

    public function getFormattedOriginalPriceAttribute(): string
    {
        return number_format($this->price, 0, ',', '.') . 'đ';
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function incrementViews(): void
    {
        $this->increment('view_count');
    }

    public function updateRating(): void
    {
        $avg = $this->reviews()->avg('rating');
        $count = $this->reviews()->count();
        $this->update(['avg_rating' => $avg ?? 0, 'review_count' => $count]);
    }
}
