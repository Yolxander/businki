<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Proposal extends Model
{
    use HasFactory;

    protected $fillable = [
        'intake_response_id',
        'client_id',
        'title',
        'description',
        'scope',
        'deliverables',
        'timeline',
        'price',
        'status',
        'user_id',
        'valid_until',
        'version',
        'terms_conditions',
        'payment_terms'
    ];

    protected $casts = [
        'deliverables' => 'array',
        'timeline' => 'array',
        'price' => 'decimal:2',
        'valid_until' => 'date',
        'terms_conditions' => 'array',
        'payment_terms' => 'array',
    ];

    public function intakeResponse()
    {
        return $this->belongsTo(IntakeResponse::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function project()
    {
        return $this->hasOne(Project::class);
    }

    public function getStatusColorAttribute()
    {
        switch ($this->status) {
            case 'accepted':
                return 'bg-green-100 text-green-800';
            case 'sent':
                return 'bg-blue-100 text-blue-800';
            case 'draft':
                return 'bg-gray-100 text-gray-800';
            case 'rejected':
                return 'bg-red-100 text-red-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    }

    public function getFormattedPriceAttribute()
    {
        return '$' . number_format($this->price, 2);
    }
}
