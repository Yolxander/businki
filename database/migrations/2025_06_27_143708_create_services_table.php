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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('profile_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('category', ['Web Design', 'Development', 'SEO', 'Branding', 'Content', 'Marketing']);
            $table->enum('pricing_type', ['Hourly', 'One Time', 'Project-based', 'Monthly']);
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->decimal('one_time_price', 10, 2)->nullable();
            $table->decimal('project_price', 10, 2)->nullable();
            $table->decimal('monthly_price', 10, 2)->nullable();
            $table->string('duration')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('profile_id')->references('id')->on('profiles')->onDelete('cascade');
            $table->index('profile_id');
            $table->index('category');
            $table->index('pricing_type');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
