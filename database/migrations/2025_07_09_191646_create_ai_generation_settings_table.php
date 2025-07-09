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
        Schema::create('ai_generation_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g., 'default', 'proposal', 'task'
            $table->string('model')->default('gpt-4');
            $table->decimal('temperature', 3, 2)->default(0.7);
            $table->integer('max_tokens')->default(4000);
            $table->decimal('top_p', 3, 2)->default(1.0);
            $table->integer('frequency_penalty')->default(0);
            $table->integer('presence_penalty')->default(0);
            $table->text('system_prompt')->nullable();
            $table->json('additional_parameters')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_generation_settings');
    }
};
