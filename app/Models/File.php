<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class File extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'folder_id',
        'provider_id',
        'project_id',
        'name',
        'type',
        'size',
        'storage_path',
        'is_starred',
    ];

    public function folder()
    {
        return $this->belongsTo(Folder::class);
    }

    public function versions()
    {
        return $this->hasMany(FileVersion::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function shares()
    {
        return $this->hasMany(FileShare::class);
    }

    public function activities()
    {
        return $this->hasMany(FileActivity::class);
    }
}
