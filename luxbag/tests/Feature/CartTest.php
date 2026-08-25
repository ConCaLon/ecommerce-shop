<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for the shopping cart.
 */
class CartTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupRoles();

        $category = Category::create([
            'name'   => 'Test Category',
            'slug'   => 'test-category',
            'status' => 'active',
        ]);

        $brand = Brand::create([
            'name'   => 'Test Brand',
            'slug'   => 'test-brand',
            'status' => 'active',
        ]);

        $this->product = Product::create([
            'category_id'  => $category->id,
            'brand_id'     => $brand->id,
            'name'         => 'Test Bag',
            'slug'         => 'test-bag',
            'sku'          => 'SKU-TEST-001',
            'price'        => 1000000,
            'sale_price'   => null,
            'stock'        => 10,
            'status'       => 'active',
            'is_featured'  => false,
            'is_new'       => false,
            'sold_count'   => 0,
            'avg_rating'   => 0,
            'review_count' => 0,
        ]);

        $this->user = User::factory()->create(['status' => 'active']);
        $this->user->assignRole('customer');
    }

    // ─── Cart Page ────────────────────────────────────────────────

    public function test_cart_page_is_accessible_for_guests(): void
    {
        $response = $this->get('/gio-hang');
        $response->assertStatus(200);
    }

    public function test_cart_page_is_accessible_for_authenticated_users(): void
    {
        $response = $this->actingAs($this->user)->get('/gio-hang');
        $response->assertStatus(200);
    }

    // ─── Add to Cart ──────────────────────────────────────────────

    public function test_authenticated_user_can_add_product_to_cart(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/gio-hang/them', [
                'product_id' => $this->product->id,
                'quantity'   => 2,
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('carts', [
            'user_id'    => $this->user->id,
            'product_id' => $this->product->id,
            'quantity'   => 2,
        ]);
    }

    public function test_adding_same_product_twice_increments_quantity(): void
    {
        // Add once
        $this->actingAs($this->user)
            ->postJson('/gio-hang/them', [
                'product_id' => $this->product->id,
                'quantity'   => 1,
            ]);

        // Add again
        $response = $this->actingAs($this->user)
            ->postJson('/gio-hang/them', [
                'product_id' => $this->product->id,
                'quantity'   => 2,
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('carts', [
            'user_id'    => $this->user->id,
            'product_id' => $this->product->id,
            'quantity'   => 3, // 1 + 2
        ]);
    }

    public function test_cannot_add_more_than_stock_to_cart(): void
    {
        // Stock is 10, validation max is 50 — use 11 to exceed stock but pass validation
        $response = $this->actingAs($this->user)
            ->postJson('/gio-hang/them', [
                'product_id' => $this->product->id,
                'quantity'   => 11, // Exceeds stock of 10
            ]);

        $response->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    public function test_cannot_add_inactive_product_to_cart(): void
    {
        $this->product->update(['status' => 'inactive']);

        $response = $this->actingAs($this->user)
            ->postJson('/gio-hang/them', [
                'product_id' => $this->product->id,
                'quantity'   => 1,
            ]);

        $response->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    public function test_cart_count_endpoint_returns_correct_count(): void
    {
        Cart::create([
            'user_id'    => $this->user->id,
            'product_id' => $this->product->id,
            'quantity'   => 3,
        ]);

        $response = $this->actingAs($this->user)->getJson('/gio-hang/count');

        $response->assertStatus(200)
            ->assertJson(['count' => 3]);
    }

    // ─── Update Cart ──────────────────────────────────────────────

    public function test_user_can_update_cart_item_quantity(): void
    {
        $cart = Cart::create([
            'user_id'    => $this->user->id,
            'product_id' => $this->product->id,
            'quantity'   => 1,
        ]);

        $response = $this->actingAs($this->user)
            ->patchJson("/gio-hang/cap-nhat/{$cart->id}", ['quantity' => 3]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('carts', [
            'id'       => $cart->id,
            'quantity' => 3,
        ]);
    }

    // ─── Remove Cart Item ─────────────────────────────────────────

    public function test_user_can_remove_item_from_cart(): void
    {
        $cart = Cart::create([
            'user_id'    => $this->user->id,
            'product_id' => $this->product->id,
            'quantity'   => 2,
        ]);

        // Without Ajax header, CartController redirects back() on delete
        $response = $this->actingAs($this->user)
            ->delete("/gio-hang/xoa/{$cart->id}");

        // The remove action redirects back for non-AJAX requests
        $this->assertContains($response->status(), [200, 302]);

        $this->assertDatabaseMissing('carts', ['id' => $cart->id]);
    }

    public function test_user_cannot_remove_another_users_cart_item(): void
    {
        $otherUser = User::factory()->create(['status' => 'active']);
        $otherUser->assignRole('customer');

        $cart = Cart::create([
            'user_id'    => $otherUser->id,
            'product_id' => $this->product->id,
            'quantity'   => 1,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/gio-hang/xoa/{$cart->id}");

        // Should 404 because cart doesn't belong to this user
        $response->assertStatus(404);
    }

    // ─── Clear Cart ───────────────────────────────────────────────

    public function test_user_can_clear_entire_cart(): void
    {
        Cart::create([
            'user_id'    => $this->user->id,
            'product_id' => $this->product->id,
            'quantity'   => 2,
        ]);

        $response = $this->actingAs($this->user)->delete('/gio-hang/xoa-het');
        $response->assertRedirect(route('cart.index'));

        $this->assertEquals(0, Cart::where('user_id', $this->user->id)->count());
    }
}
