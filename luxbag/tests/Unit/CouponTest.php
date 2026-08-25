<?php

namespace Tests\Unit;

use App\Models\Coupon;
use Tests\TestCase;

/**
 * Unit tests for Coupon model business logic.
 * These tests do NOT require a database (pure PHP logic).
 */
class CouponTest extends TestCase
{
    // ─── isValid() ────────────────────────────────────────────────

    public function test_coupon_is_invalid_when_status_is_not_active(): void
    {
        $coupon = new Coupon([
            'status'     => 'inactive',
            'expires_at' => null,
            'starts_at'  => null,
            'max_uses'   => null,
            'used_count' => 0,
        ]);

        $this->assertFalse($coupon->isValid());
    }

    public function test_coupon_is_valid_when_active_with_no_dates(): void
    {
        $coupon = new Coupon([
            'status'     => 'active',
            'expires_at' => null,
            'starts_at'  => null,
            'max_uses'   => null,
            'used_count' => 0,
        ]);

        $this->assertTrue($coupon->isValid());
    }

    public function test_coupon_is_invalid_when_expired(): void
    {
        $coupon = new Coupon([
            'status'     => 'active',
            'expires_at' => now()->subDay(),
            'starts_at'  => null,
            'max_uses'   => null,
            'used_count' => 0,
        ]);

        $this->assertFalse($coupon->isValid());
    }

    public function test_coupon_is_invalid_when_not_yet_started(): void
    {
        $coupon = new Coupon([
            'status'     => 'active',
            'expires_at' => null,
            'starts_at'  => now()->addDay(),
            'max_uses'   => null,
            'used_count' => 0,
        ]);

        $this->assertFalse($coupon->isValid());
    }

    public function test_coupon_is_invalid_when_max_uses_reached(): void
    {
        $coupon = new Coupon([
            'status'     => 'active',
            'expires_at' => null,
            'starts_at'  => null,
            'max_uses'   => 10,
            'used_count' => 10,
        ]);

        $this->assertFalse($coupon->isValid());
    }

    public function test_coupon_is_valid_when_under_max_uses(): void
    {
        $coupon = new Coupon([
            'status'     => 'active',
            'expires_at' => null,
            'starts_at'  => null,
            'max_uses'   => 10,
            'used_count' => 9,
        ]);

        $this->assertTrue($coupon->isValid());
    }

    // ─── calculateDiscount() ──────────────────────────────────────

    public function test_percent_coupon_calculates_correct_discount(): void
    {
        $coupon = new Coupon([
            'type'               => 'percent',
            'value'              => 20, // 20%
            'min_order_amount'   => 0,
            'max_discount_amount' => null,
        ]);

        // 20% of 500,000 = 100,000
        $this->assertEquals(100000.0, $coupon->calculateDiscount(500000));
    }

    public function test_percent_coupon_caps_at_max_discount_amount(): void
    {
        $coupon = new Coupon([
            'type'               => 'percent',
            'value'              => 50, // 50%
            'min_order_amount'   => 0,
            'max_discount_amount' => 100000,
        ]);

        // 50% of 500,000 = 250,000 but capped at 100,000
        $this->assertEquals(100000.0, $coupon->calculateDiscount(500000));
    }

    public function test_fixed_coupon_returns_exact_value(): void
    {
        $coupon = new Coupon([
            'type'               => 'fixed',
            'value'              => 50000,
            'min_order_amount'   => 0,
            'max_discount_amount' => null,
        ]);

        $this->assertEquals(50000.0, $coupon->calculateDiscount(200000));
    }

    public function test_fixed_coupon_cannot_exceed_subtotal(): void
    {
        $coupon = new Coupon([
            'type'               => 'fixed',
            'value'              => 500000, // discount larger than subtotal
            'min_order_amount'   => 0,
            'max_discount_amount' => null,
        ]);

        // Discount is capped at subtotal amount
        $this->assertEquals(30000.0, $coupon->calculateDiscount(30000));
    }

    public function test_coupon_returns_zero_discount_when_subtotal_below_minimum(): void
    {
        $coupon = new Coupon([
            'type'               => 'percent',
            'value'              => 20,
            'min_order_amount'   => 500000,
            'max_discount_amount' => null,
        ]);

        $this->assertEquals(0.0, $coupon->calculateDiscount(100000));
    }
}
