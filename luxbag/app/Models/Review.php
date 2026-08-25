<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'product_id', 'user_id', 'order_id', 'rating', 'title',
        'comment', 'images', 'status', 'admin_reply', 'helpful_count',
    ];

    protected $casts = ['images' => 'array', 'rating' => 'integer'];

    public function product() { return $this->belongsTo(Product::class); }
    public function user()    { return $this->belongsTo(User::class); }
    public function order()   { return $this->belongsTo(Order::class); }

    public function scopeApproved($query) { return $query->where('status', 'approved'); }

    public function getStarsHtmlAttribute(): string
    {
        $html = '';
        for ($i = 1; $i <= 5; $i++) {
            $class = $i <= $this->rating ? 'text-warning' : 'text-muted';
            $html .= "<i class='fas fa-star {$class}'></i>";
        }
        return $html;
    }
}
