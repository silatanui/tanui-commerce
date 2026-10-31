<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('status')->default('active');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['store_id', 'slug']);
            $table->index(['store_id', 'parent_id', 'status']);
        });

        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('logo')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['store_id', 'slug']);
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('sku');
            $table->text('description')->nullable();
            $table->string('short_description', 500)->nullable();
            $table->decimal('price', 12, 2);
            $table->decimal('compare_at_price', 12, 2)->nullable();
            $table->string('product_type')->default('simple');
            $table->json('specifications')->nullable();
            $table->string('status')->default('draft')->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['store_id', 'slug']);
            $table->unique(['store_id', 'sku']);
            $table->index(['store_id', 'status', 'is_featured']);
        });

        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('image_path');
            $table->string('alt_text')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('sku');
            $table->json('attributes')->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->unsignedInteger('stock_quantity')->default(0);
            $table->string('status')->default('active');
            $table->timestamps();
            $table->index(['product_id', 'status']);
        });

        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('review');
            $table->string('status')->default('pending')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_reviews');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('products');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('categories');
    }
};
