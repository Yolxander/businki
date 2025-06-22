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
        Schema::create('tools', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type', 100);
            $table->enum('status', ['active', 'inactive', 'trial', 'cancelled'])->default('active');
            $table->decimal('cost', 8, 2);
            $table->enum('billing_cycle', ['monthly', 'yearly', 'weekly', 'one-time'])->default('monthly');
            $table->date('next_billing');
            $table->string('color', 50)->nullable();
            $table->string('icon', 10)->nullable();
            $table->text('description')->nullable();
            $table->text('usage')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();

            $table->index(['status', 'type', 'next_billing']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tools');
    }
};
