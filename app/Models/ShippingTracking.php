<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingTracking extends Model
{
    use HasFactory;

    protected $table = 'shipping_tracking';

    protected $fillable = [
        'order_id',
        'awb_number',
        'status',
        'location',
        'remarks',
        'tracked_at',
        'api_response',
    ];

    protected $casts = [
        'tracked_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the order this tracking belongs to
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get formatted tracking date
     */
    public function getFormattedDateAttribute()
    {
        return $this->tracked_at->format('d M Y, h:i A');
    }

    /**
     * Get status badge color
     */
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'Dispatched' => 'primary',
            'In Transit' => 'info',
            'Out for Delivery' => 'warning',
            'Delivered' => 'success',
            'RTO' => 'danger',
            'Failed' => 'danger',
        ];

        return $badges[$this->status] ?? 'secondary';
    }

    /**
     * Scope to get latest tracking first
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('tracked_at', 'desc');
    }
}