<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 255);
            $table->string('slug', 300)->unique();
            $table->string('sku', 100)->nullable()->unique();
            $table->text('description')->nullable();
            $table->string('short_description', 500)->nullable();
            $table->decimal('price', 15, 2)->default(0);
            $table->decimal('sale_price', 15, 2)->nullable();
            $table->integer('stock')->default(0);
            $table->decimal('weight', 8, 2)->nullable();
            $table->string('material', 100)->nullable();
            $table->enum('status', ['active', 'inactive', 'draft'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_new')->default(true);
            $table->integer('sold_count')->default(0);
            $table->integer('view_count')->default(0);
            $table->decimal('avg_rating', 3, 2)->default(0.00);
            $table->integer('review_count')->default(0);
            $table->string('meta_title', 200)->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamps();

            $table->index('category_id');
            $table->index('status');
            $table->index('is_featured');
            $table->index('price');
            // SQLite (used in tests) does not support fulltext indexes
            if (DB::connection()->getDriverName() !== 'sqlite') {
                $table->fullText(['name', 'description']);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
