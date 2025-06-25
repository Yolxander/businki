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
        Schema::table('profiles', function (Blueprint $table) {
            // Add full_name (renaming existing 'name' field conceptually, but keeping both for compatibility)
            $table->string('full_name')->nullable()->after('user_id');

            // Add professional_title (renaming existing 'role' field conceptually, but keeping both for compatibility)
            $table->string('professional_title')->nullable()->after('full_name');

            // Add bio (renaming existing 'description' field conceptually, but keeping both for compatibility)
            $table->text('bio')->nullable()->after('professional_title');

            // Add hourly_rate with decimal precision for cents
            $table->decimal('hourly_rate', 8, 2)->nullable()->after('bio');

            // Add availability_status ENUM
            $table->enum('availability_status', ['available', 'busy', 'away', 'do_not_disturb'])->default('available')->after('hourly_rate');

            // Add boolean flags
            $table->boolean('profile_public')->default(false)->after('availability_status');
            $table->boolean('two_factor_enabled')->default(false)->after('profile_public');
            $table->boolean('email_notifications')->default(true)->after('two_factor_enabled');

            // Add timezone
            $table->string('timezone')->default('UTC')->after('email_notifications');

            // Add communication_preference ENUM
            $table->enum('communication_preference', ['email', 'phone', 'slack', 'teams', 'any'])->default('email')->after('timezone');

            // Add skills as JSON arrays
            $table->json('primary_skills')->nullable()->after('communication_preference');
            $table->json('secondary_skills')->nullable()->after('primary_skills');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn([
                'full_name',
                'professional_title',
                'bio',
                'hourly_rate',
                'availability_status',
                'profile_public',
                'two_factor_enabled',
                'email_notifications',
                'timezone',
                'communication_preference',
                'primary_skills',
                'secondary_skills'
            ]);
        });
    }
};
