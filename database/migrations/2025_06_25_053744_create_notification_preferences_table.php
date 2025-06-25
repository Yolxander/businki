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
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained('profiles')->onDelete('cascade');

            // Email notification preferences
            $table->boolean('email_new_projects')->default(true);
            $table->boolean('email_project_updates')->default(true);
            $table->boolean('email_task_assignments')->default(true);
            $table->boolean('email_task_completions')->default(true);
            $table->boolean('email_client_messages')->default(true);
            $table->boolean('email_payment_updates')->default(true);
            $table->boolean('email_system_alerts')->default(true);
            $table->boolean('email_marketing')->default(false);

            // Push notification preferences
            $table->boolean('push_new_projects')->default(true);
            $table->boolean('push_project_updates')->default(true);
            $table->boolean('push_task_assignments')->default(true);
            $table->boolean('push_task_completions')->default(false);
            $table->boolean('push_client_messages')->default(true);
            $table->boolean('push_payment_updates')->default(true);
            $table->boolean('push_system_alerts')->default(true);

            // SMS notification preferences
            $table->boolean('sms_urgent_alerts')->default(false);
            $table->boolean('sms_payment_updates')->default(false);
            $table->boolean('sms_client_messages')->default(false);

            // In-app notification preferences
            $table->boolean('inapp_new_projects')->default(true);
            $table->boolean('inapp_project_updates')->default(true);
            $table->boolean('inapp_task_assignments')->default(true);
            $table->boolean('inapp_task_completions')->default(true);
            $table->boolean('inapp_client_messages')->default(true);
            $table->boolean('inapp_payment_updates')->default(true);
            $table->boolean('inapp_system_alerts')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
