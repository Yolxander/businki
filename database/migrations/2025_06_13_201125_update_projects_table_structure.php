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
        Schema::table('projects', function (Blueprint $table) {
            // Add missing fields that are used in the frontend and seeder
            $table->text('description')->nullable()->after('notes');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium')->after('description');
            $table->integer('progress')->default(0)->after('priority');
            $table->string('color')->nullable()->after('progress');

            // Add client relationship (projects can be associated with clients directly)
            $table->foreignId('client_id')->nullable()->after('proposal_id')->constrained()->onDelete('set null');

            // Add start_date field
            $table->dateTime('start_date')->nullable()->after('kickoff_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropColumn([
                'description',
                'priority',
                'progress',
                'color',
                'client_id',
                'start_date'
            ]);
        });
    }
};
