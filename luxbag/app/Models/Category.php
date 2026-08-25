<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    protected $fillable = [
        'parent_id', 'name', 'slug', 'description',
        'image', 'sort_order', 'status', 'meta_title', 'meta_description',
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
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeParents($query)
    {
        return $query->whereNull('parent_id');
    }

    // ─── Helpers ──────────────────────────────────────────────────
    public function getImageUrlAttribute(): string
    {
        return $this->image
            ? asset('storage/categories/' . $this->image)
            : asset('assets/images/category-placeholder.jpg');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
