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
        Schema::create('dashboard_widgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('widget_type'); // 'quick_stats', 'recent_tasks', 'recent_projects', 'quick_actions', 'recent_proposals'
            $table->string('widget_key'); // unique identifier for the widget instance
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('configuration')->nullable(); // widget-specific configuration
            $table->json('ai_prompt')->nullable(); // the AI prompt used to generate this widget
            $table->json('ai_response')->nullable(); // the AI response that generated this widget
            $table->json('generation_metadata')->nullable(); // tokens used, cost, model, etc.
            $table->boolean('is_ai_generated')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('position')->default(0); // for ordering widgets
            $table->timestamps();
            
            $table->unique(['user_id', 'widget_key']);
            $table->index(['user_id', 'widget_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dashboard_widgets');
    }
};
