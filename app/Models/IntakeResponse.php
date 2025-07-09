<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IntakeResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'intake_id',
        'full_name',
        'company_name',
        'email',
        'project_description',
        'budget_range',
        'deadline',
        'project_type',
        'project_examples',
    ];

    protected $casts = [
        'deadline' => 'date',
        'project_examples' => 'array',
    ];

    public function intake()
    {
        return $this->belongsTo(Intake::class);
    }

    public function proposal()
    {
        return $this->hasOne(Proposal::class);
    }
}
