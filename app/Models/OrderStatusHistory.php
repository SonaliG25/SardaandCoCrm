<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderStatusHistory extends Model
{
    use HasFactory;

    protected $table = 'order_status_history'; // Add this line

    protected $fillable = [
        'order_id',
        'stage',
        'old_status',
        'new_status',
        'notes',
        'updated_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the order this history belongs to
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the user who made this update
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get stage label
     */
    public function getStageLabelAttribute()
    {
        $labels = [
            'order' => 'Order',
            'dye' => 'Dye',
            'print' => 'Print',
            'emb' => 'Embroidery',
            'master' => 'Master',
            'shipping' => 'Shipping',
            'payment' => 'Payment',
        ];

        return $labels[$this->stage] ?? ucfirst($this->stage);
    }

    /**
     * Get formatted date time
     */
    public function getFormattedDateAttribute()
    {
        return $this->created_at->format('d M Y, h:i A');
    }
}