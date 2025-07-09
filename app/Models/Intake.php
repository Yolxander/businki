<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Intake extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'client_id',
        'expiration_date',
        'status',
        'link',
    ];

    protected $casts = [
        'expiration_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function response()
    {
        return $this->hasOne(IntakeResponse::class);
    }

    public function responses()
    {
        return $this->hasMany(IntakeResponse::class);
    }

    public function forms()
    {
        return $this->hasMany(IntakeForm::class);
    }

    public function attachments()
    {
        return $this->hasMany(IntakeAttachment::class);
    }
}
