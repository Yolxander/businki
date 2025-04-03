<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('providers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->unsignedBigInteger('provider_type_id');

            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->text('description')->nullable();

            $table->timestamps();

            $table->foreign('provider_type_id')
                ->references('id')
                ->on('provider_types')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('providers');
    }
};
