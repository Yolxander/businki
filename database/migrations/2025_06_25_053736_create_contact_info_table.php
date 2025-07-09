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
        Schema::create('contact_infos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained('profiles')->onDelete('cascade');

            // Primary contact information
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable(); // VARCHAR 20 for international formats
            $table->string('location')->nullable(); // Geographic location

            // Professional profile URLs (VARCHAR 500 for long URLs)
            $table->string('linkedin_url', 500)->nullable();
            $table->string('github_url', 500)->nullable();
            $table->string('portfolio_url', 500)->nullable();
            $table->string('twitter_url', 500)->nullable();
            $table->string('instagram_url', 500)->nullable();
            $table->string('facebook_url', 500)->nullable();
            $table->string('youtube_url', 500)->nullable();
            $table->string('website_url', 500)->nullable();
            $table->string('behance_url', 500)->nullable();
            $table->string('dribbble_url', 500)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_infos');
    }
};
