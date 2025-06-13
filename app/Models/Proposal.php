<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Proposal extends Model
{
    use HasFactory;

    protected $fillable = [
        'intake_response_id',
        'scope',
        'deliverables',
        'timeline',
        'price',
        'status',
        'user_id'
    ];

    protected $casts = [
        'deliverables' => 'array',
        'timeline' => 'array',
        'price' => 'decimal:2',
    ];

    public function intakeResponse()
    {
        return $this->belongsTo(IntakeResponse::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
