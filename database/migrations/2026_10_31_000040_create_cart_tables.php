<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_id')->nullable()->index();
            $table->string('status')->default('open')->index();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->timestamps();
            $table->unique(['cart_id', 'variant_id']);
        });

        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('discount_type');
            $table->decimal('discount_value', 12, 2);
            $table->decimal('minimum_amount', 12, 2)->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->unique(['store_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
    }
};
