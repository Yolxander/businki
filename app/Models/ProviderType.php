<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProviderType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    public function providers()
    {
        return $this->hasMany(Provider::class);
    }
}
