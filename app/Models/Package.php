<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Package extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'profile_id',
        'name',
        'description',
        'type',
        'price',
        'billing_cycle',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the profile that owns the package.
     */
    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * Get the features for the package.
     */
    public function features()
    {
        return $this->hasMany(PackageFeature::class)->orderBy('sort_order');
    }

    /**
     * Validation rules for creating a package.
     */
    public static function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:Starter,Professional,Premium,Custom',
            'price' => 'required|numeric|min:0|max:999999.99',
            'billing_cycle' => 'in:One-time,Monthly,Quarterly,Yearly',
            'is_active' => 'boolean',
            'features' => 'nullable|array',
            'features.*' => 'string|max:255',
        ];
    }

    /**
     * Validation rules for updating a package.
     */
    public static function updateRules()
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'sometimes|required|in:Starter,Professional,Premium,Custom',
            'price' => 'sometimes|required|numeric|min:0|max:999999.99',
            'billing_cycle' => 'in:One-time,Monthly,Quarterly,Yearly',
            'is_active' => 'boolean',
            'features' => 'nullable|array',
            'features.*' => 'string|max:255',
        ];
    }

    /**
     * Scope to filter active packages.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter by billing cycle.
     */
    public function scopeByBillingCycle($query, $billingCycle)
    {
        return $query->where('billing_cycle', $billingCycle);
    }

    /**
     * Scope to search packages.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Boot method to handle features when package is saved.
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($package) {
            if (request()->has('features')) {
                // Delete existing features
                $package->features()->delete();

                // Create new features
                $features = request()->input('features', []);
                foreach ($features as $index => $feature) {
                    if (!empty($feature)) {
                        $package->features()->create([
                            'feature' => $feature,
                            'sort_order' => $index,
                        ]);
                    }
                }
            }
        });
    }
}
