<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WooCommerceSyncLog extends Model
{
    use HasFactory;

    protected $table = 'woocommerce_sync_logs'; // Explicitly set table name

    protected $fillable = [
        'sync_type',
        'order_id',
        'woocommerce_order_id',
        'status',
        'records_processed',
        'records_failed',
        'error_message',
        'started_at',
        'completed_at',
        'message', // Keep your old field
        'raw_data', // Keep your old field
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the order this log belongs to
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get status badge color
     */
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'completed' => 'success',
            'completed_with_errors' => 'warning',
            'failed' => 'danger',
            'processing' => 'info',
            'success' => 'success', // Keep your old status
            'partial' => 'warning', // Keep your old status
        ];

        return $badges[$this->status] ?? 'secondary';
    }

    /**
     * Get sync type label
     */
    public function getSyncTypeLabelAttribute()
    {
        $labels = [
            'pull_orders' => 'Orders Synced from WooCommerce',
            'order_imported' => 'Order Imported',
            'status_updated' => 'Status Updated to WooCommerce',
            'create' => 'Order Created', // Keep your old type
            'update' => 'Order Updated', // Keep your old type
            'status_push' => 'Status Updated to WooCommerce', // Keep your old type
        ];

        return $labels[$this->sync_type] ?? 'Unknown';
    }

    /**
     * Scope to get only failed syncs
     */
    public function scopeFailed($query)
    {
        return $query->whereIn('status', ['failed', 'completed_with_errors']);
    }

    /**
     * Scope to get only successful syncs
     */
    public function scopeSuccess($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to get processing syncs
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }
}