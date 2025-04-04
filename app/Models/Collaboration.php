<?php

// app/Models/Collaboration.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Collaboration extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'inviter_id',
        'invitee_id',
        'status',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function inviter()
    {
        return $this->belongsTo(Provider::class, 'inviter_id');
    }

    public function invitee()
    {
        return $this->belongsTo(Provider::class, 'invitee_id');
    }
}
