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
        Schema::table('proposals', function (Blueprint $table) {
            // Add missing fields that are used in the frontend
            $table->text('description')->nullable()->after('scope');
            $table->date('valid_until')->nullable()->after('timeline');
            $table->string('version')->default('1.0')->after('valid_until');
            $table->json('terms_conditions')->nullable()->after('version');
            $table->json('payment_terms')->nullable()->after('terms_conditions');

            // Add client relationship for easier access
            $table->foreignId('client_id')->nullable()->after('intake_response_id')->constrained()->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropColumn([
                'description',
                'valid_until',
                'version',
                'terms_conditions',
                'payment_terms',
                'client_id'
            ]);
        });
    }
};
