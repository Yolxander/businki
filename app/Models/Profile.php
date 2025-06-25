<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Profile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'website',
        'role',
        'description',
        'slug',
        'is_verified',
        'is_active',
        'settings',
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
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
        'settings' => 'json',
        'profile_public' => 'boolean',
        'two_factor_enabled' => 'boolean',
        'email_notifications' => 'boolean',
        'primary_skills' => 'json',
        'secondary_skills' => 'json',
        'hourly_rate' => 'decimal:2'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function contactInfo()
    {
        return $this->hasOne(ContactInfo::class);
    }

    public function notificationPreferences()
    {
        return $this->hasOne(NotificationPreferences::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($profile) {
            if (empty($profile->slug)) {
                $profile->slug = Str::slug($profile->name);
            }
        });
    }
}
