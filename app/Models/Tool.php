<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tool extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'status',
        'cost',
        'billing_cycle',
        'next_billing',
        'color',
        'icon',
        'description',
        'usage',
        'user_id'
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'next_billing' => 'date'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
