<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'zip_code',
        'company_name',
        'website',
        'description',
        'status',
        'total_revenue',
        'last_contact',
        'rating',
        'contact_person',
    ];

    protected $casts = [
        'total_revenue' => 'decimal:2',
        'last_contact' => 'date',
        'rating' => 'integer',
    ];

    public function intakes()
    {
        return $this->hasMany(Intake::class);
    }

    public function proposals()
    {
        return $this->hasMany(Proposal::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get the users associated with the client.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_client')
                    ->withTimestamps();
    }

    /**
     * Get the proposals associated with the client through intakes.
     */
    public function proposalsThroughIntakes()
    {
        return Proposal::whereHas('intakeResponse.intake', function ($query) {
            $query->where('client_id', $this->id);
        });
    }
}
