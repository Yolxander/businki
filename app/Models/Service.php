<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
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
        'category',
        'pricing_type',
        'hourly_rate',
        'one_time_price',
        'project_price',
        'monthly_price',
        'duration',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'hourly_rate' => 'decimal:2',
        'one_time_price' => 'decimal:2',
        'project_price' => 'decimal:2',
        'monthly_price' => 'decimal:2',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the profile that owns the service.
     */
    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * Get the appropriate price based on pricing type.
     */
    public function getPriceAttribute()
    {
        switch ($this->pricing_type) {
            case 'Hourly':
                return $this->hourly_rate;
            case 'One Time':
                return $this->one_time_price;
            case 'Project-based':
                return $this->project_price;
            case 'Monthly':
                return $this->monthly_price;
            default:
                return null;
        }
    }

    /**
     * Validation rules for creating a service.
     */
    public static function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|in:Web Design,Development,SEO,Branding,Content,Marketing',
            'pricing_type' => 'required|in:Hourly,One Time,Project-based,Monthly',
            'hourly_rate' => 'nullable|numeric|min:0|max:999999.99',
            'one_time_price' => 'nullable|numeric|min:0|max:999999.99',
            'project_price' => 'nullable|numeric|min:0|max:999999.99',
            'monthly_price' => 'nullable|numeric|min:0|max:999999.99',
            'duration' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Validation rules for updating a service.
     */
    public static function updateRules()
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'sometimes|required|in:Web Design,Development,SEO,Branding,Content,Marketing',
            'pricing_type' => 'sometimes|required|in:Hourly,One Time,Project-based,Monthly',
            'hourly_rate' => 'nullable|numeric|min:0|max:999999.99',
            'one_time_price' => 'nullable|numeric|min:0|max:999999.99',
            'project_price' => 'nullable|numeric|min:0|max:999999.99',
            'monthly_price' => 'nullable|numeric|min:0|max:999999.99',
            'duration' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Scope to filter active services.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by category.
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to filter by pricing type.
     */
    public function scopeByPricingType($query, $pricingType)
    {
        return $query->where('pricing_type', $pricingType);
    }

    /**
     * Scope to search services.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }
}
