<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactInfo extends Model
{
    use HasFactory;

    protected $fillable = [
        'profile_id',
        'email',
        'phone',
        'location',
        'linkedin_url',
        'github_url',
        'portfolio_url',
        'twitter_url',
        'instagram_url',
        'facebook_url',
        'youtube_url',
        'website_url',
        'behance_url',
        'dribbble_url'
    ];

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }
}
