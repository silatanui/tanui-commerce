<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('logo')->nullable();
            $table->string('favicon')->nullable();
            $table->string('primary_color', 7)->default('#16324F');
            $table->string('secondary_color', 7)->default('#C4A35A');
            $table->string('currency', 3)->default('KES');
            $table->string('status')->default('active')->index();
            $table->timestamps();
        });

        Schema::create('store_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->string('setting_key');
            $table->text('setting_value')->nullable();
            $table->timestamps();
            $table->unique(['store_id', 'setting_key']);
        });

        Schema::create('store_banners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('image')->nullable();
            $table->string('link')->nullable();
            $table->string('position')->default('hero');
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status')->default('active');
            $table->timestamps();
            $table->index(['store_id', 'position', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_banners');
        Schema::dropIfExists('store_settings');
        Schema::dropIfExists('stores');
    }
};
