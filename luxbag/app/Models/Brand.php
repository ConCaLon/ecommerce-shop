<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Brand extends Model
{
    protected $fillable = ['name', 'slug', 'logo', 'description', 'website', 'status'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
        });
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function getLogoUrlAttribute(): string
    {
        return $this->logo
            ? asset('storage/brands/' . $this->logo)
            : asset('assets/images/brand-placeholder.png');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
