<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ai_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('ai_provider_id')->constrained()->onDelete('cascade');
            $table->string('name'); // e.g., "GPT-4o - Proposal Generator"
            $table->string('model'); // e.g., "gpt-4o", "claude-3-sonnet"
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->boolean('is_default')->default(false);
            $table->integer('usage_count')->default(0);
            $table->timestamp('last_used_at')->nullable();
            $table->json('settings')->nullable(); // Model-specific settings like temperature, max_tokens, etc.
            $table->timestamps();

            $table->index(['user_id', 'is_default']);
            $table->index(['ai_provider_id']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_models');
    }
};
