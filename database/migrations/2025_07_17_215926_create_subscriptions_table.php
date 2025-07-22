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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('service_type');
            $table->enum('billing_cycle', ['monthly', 'quarterly', 'yearly'])->default('monthly');
            $table->decimal('amount', 10, 2);
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('status', ['active', 'paused', 'cancelled', 'expired'])->default('active');
            $table->date('next_billing')->nullable();
            $table->decimal('total_billed', 10, 2)->default(0);
            $table->integer('payments_received')->default(0);
            $table->json('billing_history')->nullable();
            $table->timestamps();

            // Indexes for better performance
            $table->index(['client_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index('next_billing');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
