<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationPreferences extends Model
{
    use HasFactory;

    protected $fillable = [
        'profile_id',
        // Email notifications
        'email_new_projects',
        'email_project_updates',
        'email_task_assignments',
        'email_task_completions',
        'email_client_messages',
        'email_payment_updates',
        'email_system_alerts',
        'email_marketing',
        // Push notifications
        'push_new_projects',
        'push_project_updates',
        'push_task_assignments',
        'push_task_completions',
        'push_client_messages',
        'push_payment_updates',
        'push_system_alerts',
        // SMS notifications
        'sms_urgent_alerts',
        'sms_payment_updates',
        'sms_client_messages',
        // In-app notifications
        'inapp_new_projects',
        'inapp_project_updates',
        'inapp_task_assignments',
        'inapp_task_completions',
        'inapp_client_messages',
        'inapp_payment_updates',
        'inapp_system_alerts'
    ];

    protected $casts = [
        // Email notifications
        'email_new_projects' => 'boolean',
        'email_project_updates' => 'boolean',
        'email_task_assignments' => 'boolean',
        'email_task_completions' => 'boolean',
        'email_client_messages' => 'boolean',
        'email_payment_updates' => 'boolean',
        'email_system_alerts' => 'boolean',
        'email_marketing' => 'boolean',
        // Push notifications
        'push_new_projects' => 'boolean',
        'push_project_updates' => 'boolean',
        'push_task_assignments' => 'boolean',
        'push_task_completions' => 'boolean',
        'push_client_messages' => 'boolean',
        'push_payment_updates' => 'boolean',
        'push_system_alerts' => 'boolean',
        // SMS notifications
        'sms_urgent_alerts' => 'boolean',
        'sms_payment_updates' => 'boolean',
        'sms_client_messages' => 'boolean',
        // In-app notifications
        'inapp_new_projects' => 'boolean',
        'inapp_project_updates' => 'boolean',
        'inapp_task_assignments' => 'boolean',
        'inapp_task_completions' => 'boolean',
        'inapp_client_messages' => 'boolean',
        'inapp_payment_updates' => 'boolean',
        'inapp_system_alerts' => 'boolean'
    ];

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }
}
