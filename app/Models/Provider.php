<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Provider extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_type_id',
        'user_id',
        'name',
        'email',
        'phone',
        'website',
        'description',
    ];

    public function providerType()
    {
        return $this->belongsTo(ProviderType::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function clients()
    {
        return $this->hasMany(Client::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function subtasks()
    {
        return $this->hasMany(Subtask::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Providers this one manages as team members
    public function teamMembers()
    {
        return $this->belongsToMany(
            Provider::class,
            'provider_team_members',
            'owner_provider_id',
            'member_provider_id'
        )->withPivot('role', 'status')->withTimestamps();
    }

// Teams this provider is a member of (not owner of)
    public function teams()
    {
        return $this->belongsToMany(
            Provider::class,
            'provider_team_members',
            'member_provider_id',
            'owner_provider_id'
        )->withPivot('role', 'status')->withTimestamps();
    }

}
