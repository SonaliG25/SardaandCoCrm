<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'woocommerce_customer_id',
    ];

    /**
     * Get all orders for this customer
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get total order count
     */
    public function getTotalOrdersAttribute()
    {
        return $this->orders()->count();
    }

    /**
     * Get total amount spent
     */
    public function getTotalSpentAttribute()
    {
        return $this->orders()->sum('amount');
    }

    /**
     * Get pending orders count
     */
    public function getPendingOrdersAttribute()
    {
        return $this->orders()->whereIn('order_status', ['new', 'processing'])->count();
    }

    /**
     * Scope to search customers
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
        });
    }
}