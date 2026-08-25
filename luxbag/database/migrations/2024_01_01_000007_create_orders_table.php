<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('description', 255)->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->string('estimated_days', 50)->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('order_code', 20)->unique();
            $table->enum('status', ['pending','confirmed','processing','shipping','delivered','cancelled','refunded'])->default('pending');
            $table->enum('payment_method', ['cod','bank_transfer','vnpay','momo'])->default('cod');
            $table->enum('payment_status', ['pending','paid','failed','refunded'])->default('pending');
            $table->foreignId('shipping_method_id')->nullable()->constrained()->nullOnDelete();
            $table->string('shipping_name', 150)->nullable();
            $table->string('shipping_phone', 20)->nullable();
            $table->string('shipping_email', 255)->nullable();
            $table->text('shipping_address')->nullable();
            $table->string('shipping_province', 100)->nullable();
            $table->string('shipping_district', 100)->nullable();
            $table->string('shipping_ward', 100)->nullable();
            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();
            $table->string('coupon_code', 50)->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('shipping_fee', 10, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->text('cancelled_reason')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('user_id');
            $table->index('created_at');
        });

        Schema::create('order_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->string('product_name', 255);
            $table->string('product_sku', 100)->nullable();
            $table->string('variant_info', 100)->nullable();
            $table->string('product_image', 255)->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->enum('method', ['cod','bank_transfer','vnpay','momo']);
            $table->decimal('amount', 15, 2);
            $table->enum('status', ['pending','completed','failed','refunded'])->default('pending');
            $table->string('transaction_id', 100)->nullable();
            $table->json('gateway_response')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_details');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('shipping_methods');
    }
};
