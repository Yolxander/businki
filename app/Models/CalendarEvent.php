<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalendarEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'project_id',
        'title',
        'date',
        'start_time',
        'end_time',
        'location',
        'attendees',
        'color',
    ];

    protected $casts = [
        'date' => 'date',
    ];

}
