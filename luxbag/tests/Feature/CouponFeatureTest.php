<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Cart;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for coupon / voucher application in the cart.
 */
class CouponFeatureTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupRoles();

        $category = Category::create(['name' => 'Cat', 'slug' => 'cat', 'status' => 'active']);
        $brand    = Brand::create(['name' => 'Brand', 'slug' => 'brand', 'status' => 'active']);

        $this->product = Product::create([
            'category_id'  => $category->id,
            'brand_id'     => $brand->id,
            'name'         => 'Coupon Test Bag',
            'slug'         => 'coupon-test-bag',
            'sku'          => 'CPN-001',
            'price'        => 500000,
            'sale_price'   => null,
            'stock'        => 50,
            'status'       => 'active',
            'is_featured'  => false,
            'is_new'       => false,
            'sold_count'   => 0,
            'avg_rating'   => 0,
            'review_count' => 0,
        ]);

        $this->user = User::factory()->create(['status' => 'active']);
        $this->user->assignRole('customer');

        // Add item to cart
        Cart::create([
            'user_id'    => $this->user->id,
            'product_id' => $this->product->id,
            'quantity'   => 2, // subtotal = 1,000,000
        ]);
    }

    private function makeActiveCoupon(array $overrides = []): Coupon
    {
        return Coupon::create(array_merge([
            'code'               => 'TESTCODE',
            'type'               => 'percent',
            'value'              => 10,
            'min_order_amount'   => 0,
            'max_discount_amount' => null,
            'max_uses'           => null,
            'used_count'         => 0,
            'per_user_limit'     => 1,
            'status'             => 'active',
            // SQLite doesn't support useCurrent() as column default, must provide explicitly
            'starts_at'          => now()->subDay(),
            'expires_at'         => null,
        ], $overrides));
    }

    // ─── Apply Coupon ─────────────────────────────────────────────

    public function test_user_can_apply_valid_coupon(): void
    {
        $this->makeActiveCoupon();

        $response = $this->actingAs($this->user)
            ->post('/gio-hang/ap-ma-giam-gia', ['coupon_code' => 'TESTCODE']);

        $response->assertSessionHas('success');
        $this->assertEquals('TESTCODE', session('coupon_code'));
    }

    public function test_applying_invalid_coupon_shows_error(): void
    {
        $response = $this->actingAs($this->user)
            ->post('/gio-hang/ap-ma-giam-gia', ['coupon_code' => 'INVALID123']);

        $response->assertSessionHas('error');
        $this->assertNull(session('coupon_code'));
    }

    public function test_applying_expired_coupon_shows_error(): void
    {
        $this->makeActiveCoupon([
            'code'       => 'EXPIRED10',
            'expires_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($this->user)
            ->post('/gio-hang/ap-ma-giam-gia', ['coupon_code' => 'EXPIRED10']);

        $response->assertSessionHas('error');
    }

    public function test_applying_coupon_below_minimum_order_shows_error(): void
    {
        $this->makeActiveCoupon([
            'code'             => 'MINORDER',
            'min_order_amount' => 5000000, // much higher than cart subtotal
        ]);

        $response = $this->actingAs($this->user)
            ->post('/gio-hang/ap-ma-giam-gia', ['coupon_code' => 'MINORDER']);

        $response->assertSessionHas('error');
    }

    // ─── Remove Coupon ────────────────────────────────────────────

    public function test_user_can_remove_applied_coupon(): void
    {
        // Simulate already applied coupon in session
        $response = $this->actingAs($this->user)
            ->withSession(['coupon_code' => 'TESTCODE'])
            ->delete('/gio-hang/xoa-ma-giam-gia');

        $response->assertSessionHas('success');
        $this->assertNull(session('coupon_code'));
    }
}
