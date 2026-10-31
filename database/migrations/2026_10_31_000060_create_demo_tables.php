<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demo_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_id');
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->unique(['session_id', 'store_id']);
        });

        Schema::create('demo_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->nullable()->index();
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demo_activity_logs');
        Schema::dropIfExists('demo_sessions');
    }
};
