<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->text('name')->nullable();
            $table->text('email')->nullable();
            $table->text('phone')->nullable();
            $table->text('address')->nullable();
            $table->text('description')->nullable();

            $table->unsignedBigInteger('provider_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();

            $table->timestamps();

            $table->foreign('provider_id')->references('id')->on('providers')->nullOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
