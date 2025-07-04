<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageFeature extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'package_id',
        'feature',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the package that owns the feature.
     */
    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * Validation rules for creating a package feature.
     */
    public static function rules()
    {
        return [
            'package_id' => 'required|exists:packages,id',
            'feature' => 'required|string|max:255',
            'sort_order' => 'integer|min:0',
        ];
    }

    /**
     * Validation rules for updating a package feature.
     */
    public static function updateRules()
    {
        return [
            'feature' => 'sometimes|required|string|max:255',
            'sort_order' => 'integer|min:0',
        ];
    }
}
