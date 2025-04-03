<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->text('name');
            $table->text('description')->nullable();

            $table->string('status')->default('In Progress');

            $table->timestamp('start_date')->useCurrent();
            $table->timestamp('due_date')->nullable();

            $table->unsignedBigInteger('client_id')->nullable();
            $table->unsignedBigInteger('provider_id')->nullable();

            $table->string('color')->default('bg-blue-100');

            $table->timestamps();

            $table->foreign('client_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete(); // Link to users table (clients)

            $table->foreign('provider_id')
                ->references('id')
                ->on('providers')
                ->nullOnDelete(); // Link to providers table
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
