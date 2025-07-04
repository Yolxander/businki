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
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('industry');
            $table->string('type'); // website, tool, software
            $table->boolean('featured')->default(false);
            $table->text('image');
            $table->text('description');
            $table->json('frames'); // Array of image URLs
            $table->json('features'); // Array of features
            $table->json('services'); // Array of services
            $table->string('price');
            $table->string('demo')->default('#');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
