<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->string('source_type', 40);
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('external_id', 80)->unique();
            $table->string('title');
            $table->text('content');
            $table->string('content_hash', 64);
            $table->longText('embedding')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['store_id', 'source_type']);
            $table->index(['store_id', 'content_hash']);
        });

        Schema::create('ai_chat_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->string('session_token', 64)->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });

        Schema::create('ai_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_chat_session_id')->constrained('ai_chat_sessions')->cascadeOnDelete();
            $table->string('role', 20);
            $table->text('content');
            $table->text('raw_content')->nullable();
            $table->json('sources')->nullable();
            $table->unsignedSmallInteger('latency_ms')->nullable();
            $table->timestamps();

            $table->index(['ai_chat_session_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_chat_messages');
        Schema::dropIfExists('ai_chat_sessions');
        Schema::dropIfExists('ai_documents');
    }
};
