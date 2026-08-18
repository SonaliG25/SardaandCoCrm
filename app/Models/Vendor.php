<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'contact_person',
        'phone',
        'email',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get orders where this vendor is assigned to dye stage
     */
    public function dyeOrders()
    {
        return $this->hasMany(Order::class, 'dye_vendor_id');
    }

    /**
     * Get orders where this vendor is assigned to print stage
     */
    public function printOrders()
    {
        return $this->hasMany(Order::class, 'print_vendor_id');
    }

    /**
     * Get orders where this vendor is assigned to embroidery stage
     */
    public function embOrders()
    {
        return $this->hasMany(Order::class, 'emb_vendor_id');
    }

    /**
     * Get orders where this vendor is assigned to master stage
     */
    public function masterOrders()
    {
        return $this->hasMany(Order::class, 'master_vendor_id');
    }

    /**
     * Get all orders for this vendor based on their type
     */
    public function orders()
    {
        switch ($this->type) {
            case 'dye':
                return $this->dyeOrders();
            case 'print':
                return $this->printOrders();
            case 'emb':
                return $this->embOrders();
            case 'master':
                return $this->masterOrders();
            default:
                return $this->dyeOrders(); // fallback
        }
    }

    /**
     * Get vendor type label
     */
    public function getTypeLabelAttribute()
    {
        $labels = [
            'dye' => 'Dye Vendor',
            'print' => 'Print Vendor',
            'emb' => 'Embroidery Vendor',
            'master' => 'Master Tailor',
        ];

        return $labels[$this->type] ?? 'Unknown';
    }

    /**
     * Get vendor status badge color
     */
    public function getStatusBadgeAttribute()
    {
        return $this->is_active ? 'success' : 'secondary';
    }

    /**
     * Get vendor status text
     */
    public function getStatusTextAttribute()
    {
        return $this->is_active ? 'Active' : 'Inactive';
    }

    /**
     * Scope to filter by type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get only active vendors
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to search vendors
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('contact_person', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }
}