<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    // Migration only defines created_at, not updated_at
    public $timestamps = false;
    protected $dates = ['created_at'];

    protected $fillable = [
        'user_id', 'full_name', 'phone', 'province', 'district',
        'ward', 'address_detail', 'is_default',
    ];

    protected $casts = ['is_default' => 'boolean'];

    public function user() { return $this->belongsTo(User::class); }

    public function getFullAddressAttribute(): string
    {
        return "{$this->address_detail}, {$this->ward}, {$this->district}, {$this->province}";
    }
}
