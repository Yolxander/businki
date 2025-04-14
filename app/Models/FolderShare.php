<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FolderShare extends Model
{
    use HasFactory;

    protected $fillable = [
        'folder_id',
        'shared_with_provider_id',
        'permission',
        'created_by',
    ];

    public function folder()
    {
        return $this->belongsTo(Folder::class);
    }

    public function sharedWithUser()
    {
        return $this->belongsTo(User::class, 'shared_with_user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
