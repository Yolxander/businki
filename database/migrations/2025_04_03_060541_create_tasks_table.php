<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->text('title')->nullable();
            $table->text('description')->nullable();

            $table->string('status')->default('todo');
            $table->string('priority')->default('medium');
            $table->string('category')->default('work');

            $table->timestamp('due_date')->nullable();

            $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedBigInteger('provider_id')->nullable();

            $table->boolean('completed')->default(false);

            $table->text('github_repo')->nullable();
            $table->text('tech_stack')->nullable();
            $table->text('code_snippet')->nullable();

            $table->timestamps();

            // Foreign Keys
            $table->foreign('project_id')->references('id')->on('projects')->nullOnDelete();
            $table->foreign('provider_id')->references('id')->on('providers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
