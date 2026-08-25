<?php

namespace Tests\Unit;

use App\Models\Order;
use Tests\TestCase;

/**
 * Unit tests for Order model helpers and constants.
 */
class OrderTest extends TestCase
{
    // ─── Status Labels ────────────────────────────────────────────

    public function test_order_status_labels_are_defined(): void
    {
        $this->assertArrayHasKey('pending',    Order::STATUS_LABELS);
        $this->assertArrayHasKey('confirmed',  Order::STATUS_LABELS);
        $this->assertArrayHasKey('processing', Order::STATUS_LABELS);
        $this->assertArrayHasKey('shipping',   Order::STATUS_LABELS);
        $this->assertArrayHasKey('delivered',  Order::STATUS_LABELS);
        $this->assertArrayHasKey('cancelled',  Order::STATUS_LABELS);
        $this->assertArrayHasKey('refunded',   Order::STATUS_LABELS);
    }

    public function test_status_label_attribute_returns_correct_label(): void
    {
        $order = new Order(['status' => 'pending']);
        $this->assertEquals('Chờ xác nhận', $order->status_label);
    }

    public function test_status_label_returns_raw_status_for_unknown_value(): void
    {
        $order = new Order(['status' => 'unknown_status']);
        $this->assertEquals('unknown_status', $order->status_label);
    }

    public function test_status_color_returns_correct_color(): void
    {
        $order = new Order(['status' => 'delivered']);
        $this->assertEquals('success', $order->status_color);
    }

    public function test_status_color_returns_secondary_for_unknown(): void
    {
        $order = new Order(['status' => 'invalid']);
        $this->assertEquals('secondary', $order->status_color);
    }

    // ─── Payment Method Label ─────────────────────────────────────

    public function test_payment_method_label_for_cod(): void
    {
        $order = new Order(['payment_method' => 'cod']);
        $this->assertEquals('Thanh toán khi nhận hàng', $order->payment_method_label);
    }

    public function test_payment_method_label_for_vnpay(): void
    {
        $order = new Order(['payment_method' => 'vnpay']);
        $this->assertEquals('VNPay', $order->payment_method_label);
    }

    public function test_payment_method_label_for_bank_transfer(): void
    {
        $order = new Order(['payment_method' => 'bank_transfer']);
        $this->assertEquals('Chuyển khoản ngân hàng', $order->payment_method_label);
    }

    // ─── canBeCancelled() ─────────────────────────────────────────

    public function test_pending_order_can_be_cancelled(): void
    {
        $order = new Order(['status' => 'pending']);
        $this->assertTrue($order->canBeCancelled());
    }

    public function test_confirmed_order_can_be_cancelled(): void
    {
        $order = new Order(['status' => 'confirmed']);
        $this->assertTrue($order->canBeCancelled());
    }

    public function test_shipping_order_cannot_be_cancelled(): void
    {
        $order = new Order(['status' => 'shipping']);
        $this->assertFalse($order->canBeCancelled());
    }

    public function test_delivered_order_cannot_be_cancelled(): void
    {
        $order = new Order(['status' => 'delivered']);
        $this->assertFalse($order->canBeCancelled());
    }

    // ─── canBeReviewed() ──────────────────────────────────────────

    public function test_delivered_order_can_be_reviewed(): void
    {
        $order = new Order(['status' => 'delivered']);
        $this->assertTrue($order->canBeReviewed());
    }

    public function test_pending_order_cannot_be_reviewed(): void
    {
        $order = new Order(['status' => 'pending']);
        $this->assertFalse($order->canBeReviewed());
    }

    public function test_cancelled_order_cannot_be_reviewed(): void
    {
        $order = new Order(['status' => 'cancelled']);
        $this->assertFalse($order->canBeReviewed());
    }
}
