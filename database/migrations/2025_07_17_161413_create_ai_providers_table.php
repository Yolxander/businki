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
        Schema::create('ai_providers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name'); // e.g., "My OpenAI Account", "Company AIMLAPI"
            $table->string('provider_type'); // e.g., "AIMLAPI", "OpenAI", "Anthropic", "Google"
            $table->text('api_key'); // Encrypted API key
            $table->string('base_url')->nullable(); // e.g., "https://api.aimlapi.com/v1"
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->json('settings')->nullable(); // Additional provider-specific settings
            $table->integer('usage_count')->default(0);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'provider_type']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_providers');
    }
};
