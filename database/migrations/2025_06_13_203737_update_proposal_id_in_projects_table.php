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
//        Schema::table('projects', function (Blueprint $table) {
//            // First drop the existing column
//            $table->dropColumn('proposal_id');
//
//            // Add the new foreign key column
//            $table->foreignId('proposal_id')->after('id')->constrained('proposals')->onDelete('cascade');
//        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
//        Schema::table('projects', function (Blueprint $table) {
//            // Remove the foreign key constraint
//            $table->dropForeign(['proposal_id']);
//
//            // Drop the column
//            $table->dropColumn('proposal_id');
//
//            // Add back the original string column
//            $table->string('proposal_id')->after('id');
//        });
    }
};
