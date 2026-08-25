<?php

namespace Tests\Unit;

use App\Models\Product;
use Tests\TestCase;

/**
 * Unit tests for Product model accessors and helpers.
 */
class ProductTest extends TestCase
{
    // ─── getCurrentPriceAttribute() ───────────────────────────────

    public function test_current_price_returns_sale_price_when_set(): void
    {
        $product = new Product([
            'price'      => 1000000,
            'sale_price' => 800000,
        ]);

        $this->assertEquals(800000.0, $product->current_price);
    }

    public function test_current_price_falls_back_to_price_when_no_sale(): void
    {
        $product = new Product([
            'price'      => 1000000,
            'sale_price' => null,
        ]);

        $this->assertEquals(1000000.0, $product->current_price);
    }

    // ─── getDiscountPercentAttribute() ────────────────────────────

    public function test_discount_percent_calculates_correctly(): void
    {
        $product = new Product([
            'price'      => 1000000,
            'sale_price' => 800000,
        ]);

        // (1 - 800000/1000000) * 100 = 20%
        $this->assertEquals(20, $product->discount_percent);
    }

    public function test_discount_percent_is_zero_when_no_sale_price(): void
    {
        $product = new Product([
            'price'      => 1000000,
            'sale_price' => null,
        ]);

        $this->assertEquals(0, $product->discount_percent);
    }

    public function test_discount_percent_is_zero_when_price_is_zero(): void
    {
        $product = new Product([
            'price'      => 0,
            'sale_price' => 0,
        ]);

        $this->assertEquals(0, $product->discount_percent);
    }

    // ─── getIsOnSaleAttribute() ───────────────────────────────────

    public function test_product_is_on_sale_when_sale_price_is_lower(): void
    {
        $product = new Product([
            'price'      => 1000000,
            'sale_price' => 800000,
        ]);

        $this->assertTrue($product->is_on_sale);
    }

    public function test_product_is_not_on_sale_when_no_sale_price(): void
    {
        $product = new Product([
            'price'      => 1000000,
            'sale_price' => null,
        ]);

        $this->assertFalse($product->is_on_sale);
    }

    public function test_product_is_not_on_sale_when_sale_price_equals_price(): void
    {
        // sale_price is null means no sale; if same value, still on sale per logic
        $product = new Product([
            'price'      => 1000000,
            'sale_price' => 1000000, // Same — technically not a discount
        ]);

        // Model checks sale_price !== null AND sale_price < price
        $this->assertFalse($product->is_on_sale);
    }

    // ─── getFormattedPriceAttribute() ────────────────────────────

    public function test_formatted_price_includes_dong_symbol(): void
    {
        $product = new Product([
            'price'      => 1850000,
            'sale_price' => 1480000,
        ]);

        $this->assertStringEndsWith('đ', $product->formatted_price);
    }

    public function test_formatted_original_price_includes_dong_symbol(): void
    {
        $product = new Product([
            'price'      => 1850000,
            'sale_price' => null,
        ]);

        $this->assertStringEndsWith('đ', $product->formatted_original_price);
        $this->assertStringContainsString('1.850.000', $product->formatted_original_price);
    }
}
