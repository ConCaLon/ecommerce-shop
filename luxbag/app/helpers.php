<?php

if (!function_exists('vnd')) {
    /**
     * Format number as Vietnamese Dong currency.
     * Returns '0đ' when null is passed (e.g. when no products exist).
     */
    function vnd(float|int|string|null $amount): string
    {
        return number_format((float)($amount ?? 0), 0, ',', '.') . 'đ';
    }
}

if (!function_exists('setting')) {
    /**
     * Get a site setting value
     */
    function setting(string $key, $default = null): mixed
    {
        static $settings = null;
        if ($settings === null) {
            try {
                $settings = \App\Models\Setting::pluck('value', 'key')->toArray();
            } catch (\Exception $e) {
                return $default;
            }
        }
        return $settings[$key] ?? $default;
    }
}

if (!function_exists('activity_log')) {
    /**
     * Log an admin activity
     */
    function activity_log(?int $userId, string $action, ?string $modelType = null, ?int $modelId = null, ?string $description = null): void
    {
        try {
            \App\Models\ActivityLog::create([
                'user_id'    => $userId,
                'action'     => $action,
                'model_type' => $modelType,
                'model_id'   => $modelId,
                'description' => $description,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            // Silently fail to not break main flow
        }
    }
}

if (!function_exists('cart_count')) {
    /**
     * Get current cart item count
     */
    function cart_count(): int
    {
        try {
            $query = \App\Models\Cart::query();
            if (auth()->check()) {
                $query->where('user_id', auth()->id());
            } else {
                $query->where('session_id', session()->getId());
            }
            return (int) $query->sum('quantity');
        } catch (\Exception $e) {
            return 0;
        }
    }
}

if (!function_exists('wishlist_count')) {
    /**
     * Get current user wishlist count
     */
    function wishlist_count(): int
    {
        if (!auth()->check()) return 0;
        try {
            return \App\Models\Wishlist::where('user_id', auth()->id())->count();
        } catch (\Exception $e) {
            return 0;
        }
    }
}
