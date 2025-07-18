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
        Schema::create('context_engineering_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dev_project_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['implementation', 'workflow', 'project_structure', 'ui_ux', 'bug_tracking', 'custom']);
            $table->text('content');
            $table->string('file_path')->nullable(); // For stored files
            $table->string('file_name')->nullable(); // Original filename
            $table->string('mime_type')->nullable(); // File type
            $table->integer('file_size')->nullable(); // File size in bytes
            $table->boolean('is_generated')->default(false); // Whether AI generated
            $table->json('generation_metadata')->nullable(); // AI generation details
            $table->json('variables')->nullable(); // Template variables
            $table->boolean('is_template')->default(false); // Whether it's a template
            $table->boolean('is_active')->default(true); // Whether it's active
            $table->integer('version')->default(1); // Document version
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Indexes for better performance
            $table->index(['dev_project_id', 'type']);
            $table->index(['dev_project_id', 'is_active']);
            $table->index(['type', 'is_template']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('context_engineering_documents');
    }
};
