<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for product browsing — listing, search, filtering.
 */
class ProductBrowsingTest extends TestCase
{
    use RefreshDatabase;

    private Category $category;
    private Brand $brand;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::create([
            'name'   => 'Túi Xách Nữ',
            'slug'   => 'tui-xach-nu',
            'status' => 'active',
        ]);

        $this->brand = Brand::create([
            'name'   => 'LuxBag Original',
            'slug'   => 'luxbag-original',
            'status' => 'active',
        ]);
    }

    private function makeProduct(array $attrs = []): Product
    {
        return Product::create(array_merge([
            'category_id'       => $this->category->id,
            'brand_id'          => $this->brand->id,
            'name'              => 'Test Product',
            'slug'              => 'test-product-' . uniqid(),
            'sku'               => 'SKU-' . uniqid(),
            'price'             => 1000000,
            'sale_price'        => null,
            'stock'             => 50,
            'status'            => 'active',
            'is_featured'       => false,
            'is_new'            => false,
            'sold_count'        => 0,
            'avg_rating'        => 0,
            'review_count'      => 0,
        ], $attrs));
    }

    // ─── Products Index ───────────────────────────────────────────

    public function test_products_index_page_is_accessible(): void
    {
        $response = $this->get('/san-pham');
        $response->assertStatus(200);
    }

    // ─── Product Detail ───────────────────────────────────────────

    public function test_product_detail_page_returns_200(): void
    {
        $product = $this->makeProduct(['name' => 'Túi Tote Cao Cấp', 'slug' => 'tui-tote-cao-cap']);

        $response = $this->get('/san-pham/tui-tote-cao-cap');
        $response->assertStatus(200);
    }

    public function test_inactive_product_returns_404(): void
    {
        $this->makeProduct(['slug' => 'inactive-product', 'status' => 'inactive']);

        // ProductController should 404 for inactive products
        $response = $this->get('/san-pham/inactive-product');
        $response->assertStatus(404);
    }

    // ─── Category Filter ──────────────────────────────────────────

    public function test_category_page_is_accessible(): void
    {
        $response = $this->get('/san-pham/danh-muc/tui-xach-nu');
        $response->assertStatus(200);
    }

    // ─── Brand Filter ─────────────────────────────────────────────

    public function test_brand_page_is_accessible(): void
    {
        $response = $this->get('/san-pham/thuong-hieu/luxbag-original');
        $response->assertStatus(200);
    }

    // ─── Search ───────────────────────────────────────────────────

    public function test_search_page_is_accessible_with_no_query(): void
    {
        // Keyword < 2 chars → ProductController returns empty results without fulltext query
        $response = $this->get('/tim-kiem?q=a');
        $response->assertStatus(200);
    }

    public function test_search_page_accessible_with_empty_q(): void
    {
        // No query — renders search page with empty results
        $response = $this->get('/tim-kiem');
        $response->assertStatus(200);
    }
}
