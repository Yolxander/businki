<?php

// app/Models/ProjectTimelineEvent.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProjectTimelineEvent extends Model
{
    use HasFactory;

    public $table = 'project_timeline_events';

    protected $fillable = [
        'project_id',
        'title',
        'description',
        'event_date',
        'event_type',
        'created_by',
    ];
}
