<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'avatar',
        'gender', 'date_of_birth', 'status',
        'email_verified_at', 'email_verify_token',
        'reset_token', 'reset_token_expires', 'google_id',
    ];

    protected $hidden = [
        'password', 'remember_token', 'email_verify_token',
        'reset_token', 'google_id',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'reset_token_expires' => 'datetime',
            'date_of_birth' => 'date',
            'password' => 'hashed',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────
    public function addresses()
    {
        return $this->hasMany(UserAddress::class);
    }

    public function defaultAddress()
    {
        return $this->hasOne(UserAddress::class)->where('is_default', true);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function wishlistProducts()
    {
        return $this->belongsToMany(Product::class, 'wishlists');
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function notifications()
    {
        return $this->hasMany(\App\Models\Notification::class);
    }

    // ─── Helpers ──────────────────────────────────────────────────
    public function isAdmin(): bool
    {
        return $this->hasRole(['admin', 'super-admin']);
    }

    public function isStaff(): bool
    {
        return $this->hasRole(['admin', 'super-admin', 'manager', 'staff']);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isEmailVerified(): bool
    {
        return $this->email_verified_at !== null;
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar && $this->avatar !== 'default-avatar.png') {
            return asset('storage/avatars/' . $this->avatar);
        }
        return asset('assets/images/default-avatar.png');
    }

    public function getUnreadNotificationsCountAttribute(): int
    {
        return $this->notifications()->where('is_read', false)->count();
    }
}
