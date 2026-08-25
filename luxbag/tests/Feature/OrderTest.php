<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for order placement (checkout) and order management.
 */
class OrderTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Product $product;
    private ShippingMethod $shippingMethod;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupRoles();

        $category = Category::create([
            'name' => 'Test Cat', 'slug' => 'test-cat', 'status' => 'active',
        ]);
        $brand = Brand::create([
            'name' => 'Test Brand', 'slug' => 'test-brand', 'status' => 'active',
        ]);

        $this->product = Product::create([
            'category_id'  => $category->id,
            'brand_id'     => $brand->id,
            'name'         => 'Test Bag',
            'slug'         => 'test-bag',
            'sku'          => 'TEST-001',
            'price'        => 1000000,
            'sale_price'   => null,
            'stock'        => 20,
            'status'       => 'active',
            'is_featured'  => false,
            'is_new'       => false,
            'sold_count'   => 0,
            'avg_rating'   => 0,
            'review_count' => 0,
        ]);

        $this->shippingMethod = ShippingMethod::create([
            'name'           => 'Giao hàng tiêu chuẩn',
            'price'          => 30000,
            'estimated_days' => '3-5 ngày',
            'status'         => 'active',
        ]);

        $this->user = User::factory()->create(['status' => 'active']);
        $this->user->assignRole('customer');
    }

    // ─── Checkout Page ────────────────────────────────────────────

    public function test_checkout_redirects_guests_to_login(): void
    {
        $response = $this->get('/dat-hang/thanh-toan');
        $response->assertRedirect('/dang-nhap');
    }

    public function test_checkout_redirects_to_cart_when_empty(): void
    {
        $response = $this->actingAs($this->user)->get('/dat-hang/thanh-toan');
        $response->assertRedirect(route('cart.index'));
    }

    public function test_checkout_page_accessible_with_items_in_cart(): void
    {
        Cart::create([
            'user_id'    => $this->user->id,
            'product_id' => $this->product->id,
            'quantity'   => 1,
        ]);

        $response = $this->actingAs($this->user)->get('/dat-hang/thanh-toan');
        $response->assertStatus(200);
    }

    // ─── Place Order ──────────────────────────────────────────────

    public function test_user_can_place_an_order(): void
    {
        Cart::create([
            'user_id'    => $this->user->id,
            'product_id' => $this->product->id,
            'quantity'   => 2,
        ]);

        $response = $this->actingAs($this->user)
            ->post('/dat-hang/dat-hang', [
                'shipping_name'           => 'Nguyen Van Test',
                'shipping_phone'          => '0900000001',
                'shipping_email'          => 'test@example.com',
                'shipping_province'       => 'Hà Nội',
                'shipping_district'       => 'Đống Đa',
                'shipping_ward'           => 'Phương Liên',
                'shipping_address_detail' => '123 Đường Test',
                'shipping_method_id'      => $this->shippingMethod->id,
                'payment_method'          => 'cod',
            ]);

        // Should redirect to order confirmation
        $response->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'user_id'        => $this->user->id,
            'payment_method' => 'cod',
            'status'         => 'pending',
        ]);

        // Cart should be cleared
        $this->assertEquals(0, Cart::where('user_id', $this->user->id)->count());
    }

    public function test_placing_order_decrements_product_stock(): void
    {
        $initialStock = $this->product->stock; // 20

        Cart::create([
            'user_id'    => $this->user->id,
            'product_id' => $this->product->id,
            'quantity'   => 3,
        ]);

        $this->actingAs($this->user)
            ->post('/dat-hang/dat-hang', [
                'shipping_name'           => 'Nguyen Van Test',
                'shipping_phone'          => '0900000001',
                'shipping_email'          => 'test@example.com',
                'shipping_province'       => 'Hà Nội',
                'shipping_district'       => 'Đống Đa',
                'shipping_ward'           => 'Phương Liên',
                'shipping_address_detail' => '123 Đường Test',
                'shipping_method_id'      => $this->shippingMethod->id,
                'payment_method'          => 'cod',
            ]);

        $this->product->refresh();
        $this->assertEquals($initialStock - 3, $this->product->stock);
    }

    public function test_order_store_fails_with_missing_required_fields(): void
    {
        Cart::create([
            'user_id'    => $this->user->id,
            'product_id' => $this->product->id,
            'quantity'   => 1,
        ]);

        $response = $this->actingAs($this->user)
            ->post('/dat-hang/dat-hang', [
                // Missing required fields
                'payment_method' => 'cod',
            ]);

        $response->assertSessionHasErrors([
            'shipping_name', 'shipping_phone',
        ]);
    }

    // ─── Order History ────────────────────────────────────────────

    public function test_user_can_view_order_history(): void
    {
        $response = $this->actingAs($this->user)->get('/dat-hang/lich-su');
        $response->assertStatus(200);
    }

    // ─── Order Cancellation ───────────────────────────────────────

    public function test_user_can_cancel_pending_order(): void
    {
        $order = Order::create([
            'user_id'         => $this->user->id,
            'order_code'      => 'LB20240101TEST',
            'status'          => 'pending',
            'payment_method'  => 'cod',
            'payment_status'  => 'pending',
            'subtotal'        => 1000000,
            'shipping_fee'    => 30000,
            'discount_amount' => 0,
            'total'           => 1030000,
        ]);

        $response = $this->actingAs($this->user)
            ->post("/dat-hang/{$order->order_code}/huy", [
                'reason' => 'Tôi muốn hủy đơn',
            ]);

        $response->assertRedirect();

        $order->refresh();
        $this->assertEquals('cancelled', $order->status);
    }

    public function test_user_cannot_cancel_delivered_order(): void
    {
        $order = Order::create([
            'user_id'         => $this->user->id,
            'order_code'      => 'LB20240101DLV',
            'status'          => 'delivered',
            'payment_method'  => 'cod',
            'payment_status'  => 'paid',
            'subtotal'        => 1000000,
            'shipping_fee'    => 30000,
            'discount_amount' => 0,
            'total'           => 1030000,
        ]);

        $response = $this->actingAs($this->user)
            ->post("/dat-hang/{$order->order_code}/huy", []);

        $response->assertSessionHas('error');

        $order->refresh();
        $this->assertEquals('delivered', $order->status); // Unchanged
    }

    public function test_user_cannot_view_another_users_order(): void
    {
        $otherUser = User::factory()->create(['status' => 'active']);
        $otherUser->assignRole('customer');

        $order = Order::create([
            'user_id'         => $otherUser->id,
            'order_code'      => 'LB20240101OTHER',
            'status'          => 'pending',
            'payment_method'  => 'cod',
            'payment_status'  => 'pending',
            'subtotal'        => 500000,
            'shipping_fee'    => 30000,
            'discount_amount' => 0,
            'total'           => 530000,
        ]);

        $response = $this->actingAs($this->user)
            ->get("/dat-hang/{$order->order_code}");

        $response->assertStatus(404);
    }
}
