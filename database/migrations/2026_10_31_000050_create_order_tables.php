<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('order_number')->unique();
            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('shipping_cost', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->string('currency', 3)->default('KES');
            $table->string('status')->default('confirmed')->index();
            $table->string('payment_status')->default('not_processed');
            $table->boolean('is_demo')->default(true);
            $table->json('customer_details')->nullable();
            $table->json('shipping_details')->nullable();
            $table->string('coupon_code')->nullable();
            $table->timestamps();
            $table->index(['store_id', 'status', 'created_at']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->string('product_name');
            $table->string('sku');
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->json('attributes')->nullable();
            $table->timestamps();
        });

        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('shipping_method');
            $table->decimal('shipping_cost', 12, 2)->default(0);
            $table->string('status')->default('pending');
            $table->string('tracking_reference')->nullable();
            $table->timestamps();
        });

        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->integer('quantity');
            $table->string('movement_type');
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['store_id', 'movement_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
        Schema::dropIfExists('shipments');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
