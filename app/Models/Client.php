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
    ];

    public function intakes()
    {
        return $this->hasMany(Intake::class);
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
}
