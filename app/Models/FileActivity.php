<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FileActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_id',
        'provider_id',
        'action',
        'description',
    ];

    /**
     * Get the file this activity is related to.
     */
    public function file()
    {
        return $this->belongsTo(File::class);
    }

    /**
     * Get the user who performed the activity.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
