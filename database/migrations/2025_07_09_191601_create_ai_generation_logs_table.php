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
        Schema::create('ai_generation_logs', function (Blueprint $table) {
            $table->id();
            $table->string('generation_type'); // proposal, project, task, etc.
            $table->text('prompt');
            $table->longText('response');
            $table->string('model')->default('gpt-4');
            $table->integer('prompt_tokens')->nullable();
            $table->integer('completion_tokens')->nullable();
            $table->integer('total_tokens')->nullable();
            $table->decimal('temperature', 3, 2)->default(0.7);
            $table->integer('max_tokens')->nullable();
            $table->integer('execution_time_ms')->nullable();
            $table->string('status')->default('success'); // success, error, partial
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable(); // Additional context, user info, etc.
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();

            $table->index(['generation_type', 'created_at']);
            $table->index(['status', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_generation_logs');
    }
};
