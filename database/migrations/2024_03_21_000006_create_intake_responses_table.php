<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('intake_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('intake_id')->constrained()->onDelete('cascade');
            $table->string('full_name');
            $table->string('company_name');
            $table->string('email');
            $table->text('project_description');
            $table->string('budget_range');
            $table->date('deadline');
            $table->string('project_type');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intake_responses');
    }
};
