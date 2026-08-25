<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'title', 'subtitle', 'image', 'link', 'position',
        'sort_order', 'status', 'starts_at', 'ends_at',
    ];

    protected $casts = ['starts_at' => 'datetime', 'ends_at' => 'datetime'];

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            });
    }

    public function scopeHero($query) { return $query->where('position', 'hero'); }

    public function getImageUrlAttribute(): string
    {
        return asset('storage/' . $this->image);
    }
}
