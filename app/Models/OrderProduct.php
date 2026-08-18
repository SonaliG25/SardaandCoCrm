<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderProduct extends Model
{
    protected $fillable = [
        'order_id',
        'product_name',
        'product_image',
        'product_sku',
        'quantity',
        'price',
        'dye_status',
        'dye_vendor_id',
        'dye_received_date',
        'print_status',
        'print_vendor_id',
        'print_received_date',
        'emb_status',
        'emb_vendor_id',
        'emb_received_date',
        'master_status',
        'master_vendor_id',
        'master_received_date',
    ];

    protected $casts = [
        'dye_received_date' => 'date',
        'print_received_date' => 'date',
        'emb_received_date' => 'date',
        'master_received_date' => 'date',
        'price' => 'decimal:2',
    ];

    // Relationships
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function dyeVendor()
    {
        return $this->belongsTo(Vendor::class, 'dye_vendor_id');
    }

    public function printVendor()
    {
        return $this->belongsTo(Vendor::class, 'print_vendor_id');
    }

    public function embVendor()
    {
        return $this->belongsTo(Vendor::class, 'emb_vendor_id');
    }

    public function masterVendor()
    {
        return $this->belongsTo(Vendor::class, 'master_vendor_id');
    }
}