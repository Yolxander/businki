<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'client_id',
        'user_id',
        'service_type',
        'billing_cycle',
        'amount',
        'description',
        'start_date',
        'end_date',
        'status',
        'next_billing',
        'total_billed',
        'payments_received',
        'billing_history'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'total_billed' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'next_billing' => 'date',
        'payments_received' => 'integer',
        'billing_history' => 'array',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusColorAttribute()
    {
        switch ($this->status) {
            case 'active':
                return 'bg-green-100 text-green-800';
            case 'paused':
                return 'bg-yellow-100 text-yellow-800';
            case 'cancelled':
                return 'bg-red-100 text-red-800';
            case 'expired':
                return 'bg-gray-100 text-gray-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    }

    public function getBillingCycleColorAttribute()
    {
        switch ($this->billing_cycle) {
            case 'monthly':
                return 'bg-blue-100 text-blue-800';
            case 'quarterly':
                return 'bg-purple-100 text-purple-800';
            case 'yearly':
                return 'bg-indigo-100 text-indigo-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    }

    public function getFormattedAmountAttribute()
    {
        return '$' . number_format($this->amount, 2);
    }

    public function getFormattedTotalBilledAttribute()
    {
        return '$' . number_format($this->total_billed, 2);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeDueThisMonth($query)
    {
        return $query->where('status', 'active')
                    ->where('next_billing', '<=', now()->endOfMonth());
    }
}
