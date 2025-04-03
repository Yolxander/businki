<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('subtasks', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->text('title')->nullable();
            $table->text('description')->nullable();

            $table->string('status')->default('todo');
            $table->boolean('completed')->default(false);

            $table->unsignedBigInteger('task_id')->nullable();
            $table->unsignedBigInteger('provider_id')->nullable();

            $table->text('code_snippet')->nullable();
            $table->text('language')->nullable();

            $table->timestamps();

            // Foreign Keys
            $table->foreign('task_id')->references('id')->on('tasks')->nullOnDelete();
            $table->foreign('provider_id')->references('id')->on('providers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subtasks');
    }
};
