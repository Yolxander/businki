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
        Schema::table('clients', function (Blueprint $table) {
            // Add missing fields that are used in the frontend and seeder
            $table->string('website')->nullable()->after('zip_code');
            $table->text('description')->nullable()->after('website');
            $table->enum('status', ['active', 'prospect', 'inactive'])->default('active')->after('description');
            $table->decimal('total_revenue', 10, 2)->default(0)->after('status');
            $table->date('last_contact')->nullable()->after('total_revenue');
            $table->integer('rating')->default(0)->after('last_contact');

            // Add contact person fields
            $table->string('contact_person')->nullable()->after('rating');

            // Add user relationship (many-to-many is handled by user_client table)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'website',
                'description',
                'status',
                'total_revenue',
                'last_contact',
                'rating',
                'contact_person'
            ]);
        });
    }
};
