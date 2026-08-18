<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingPartner extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'api_endpoint',
        'api_key',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'api_key',
    ];

    /**
     * Get all orders for this shipping partner
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get pending shipments count
     */
    public function getPendingShipmentsAttribute()
    {
        return $this->orders()
            ->whereIn('shipping_status', ['dispatched', 'in_transit', 'out_for_delivery'])
            ->count();
    }

    /**
     * Get delivered shipments count
     */
    public function getDeliveredShipmentsAttribute()
    {
        return $this->orders()
            ->where('shipping_status', 'delivered')
            ->count();
    }

    /**
     * Scope to get only active shipping partners
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}