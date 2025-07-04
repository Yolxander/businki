<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Listing extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'industry',
        'type',
        'featured',
        'image',
        'description',
        'frames',
        'features',
        'services',
        'price',
        'demo'
    ];

    protected $casts = [
        'featured' => 'boolean',
        'frames' => 'array',
        'features' => 'array',
        'services' => 'array'
    ];
}
