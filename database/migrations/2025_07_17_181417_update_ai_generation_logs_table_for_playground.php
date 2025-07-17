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
        Schema::table('ai_generation_logs', function (Blueprint $table) {
            // Drop old columns
            $table->dropColumn([
                'generation_type',
                'model',
                'prompt_tokens',
                'completion_tokens',
                'total_tokens',
                'temperature',
                'max_tokens',
                'execution_time_ms',
                'error_message'
            ]);

            // Add new columns
            $table->foreignId('model_id')->nullable()->constrained('ai_models')->onDelete('set null');
            $table->integer('tokens_used')->nullable();
            $table->string('cost')->default('$0.00');
            $table->foreignId('template_id')->nullable()->constrained('prompt_templates')->onDelete('set null');
            $table->integer('execution_time')->nullable(); // in milliseconds
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_generation_logs', function (Blueprint $table) {
            // Drop new columns
            $table->dropForeign(['model_id']);
            $table->dropColumn(['model_id', 'tokens_used', 'cost', 'template_id', 'execution_time']);

            // Add back old columns
            $table->string('generation_type');
            $table->string('model')->default('gpt-4');
            $table->integer('prompt_tokens')->nullable();
            $table->integer('completion_tokens')->nullable();
            $table->integer('total_tokens')->nullable();
            $table->decimal('temperature', 3, 2)->default(0.7);
            $table->integer('max_tokens')->nullable();
            $table->integer('execution_time_ms')->nullable();
            $table->text('error_message')->nullable();
        });
    }
};
