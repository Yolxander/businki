<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proposal_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposal_id')->constrained()->onDelete('cascade');
            $table->longText('scope_of_work')->nullable();
            $table->longText('deliverables')->nullable();
            $table->date('timeline_start')->nullable();
            $table->date('timeline_end')->nullable();
            $table->decimal('total_price', 10, 2)->nullable();
            $table->text('payment_schedule')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposal_contents');
    }
};
