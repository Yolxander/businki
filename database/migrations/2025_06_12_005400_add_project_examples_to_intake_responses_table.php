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
        Schema::table('intake_responses', function (Blueprint $table) {
            $table->json('project_examples')->nullable()->after('project_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('intake_responses', function (Blueprint $table) {
            $table->dropColumn('project_examples');
        });
    }
};
