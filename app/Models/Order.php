<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_id',
        'customer_id',
        'order_date',
        'amount',
        
        // WooCommerce Integration
        'woocommerce_order_id',
        'woocommerce_raw_data',
        
        // Product Details
        'product_image',
        'product_description',
        
        // Workflow Stages - Dye
        'dye_vendor_id',
        'dye_status',
        'dye_received_date',
        
        // Workflow Stages - Print
        'print_vendor_id',
        'print_status',
        'print_received_date',
        
        // Workflow Stages - Embroidery
        'emb_vendor_id',
        'emb_status',
        'emb_received_date',
        
        // Workflow Stages - Master
        'master_vendor_id',
        'master_status',
        'master_received_date',
        
        // Shipping Details
        'shipping_partner_id',
        'awb_number',
        'dispatched_date',
        'shipping_status',
        'delivered_date',
        
        // Overall Status
        'order_status',
        'remark',
        // Payment
        'payment_status',
        'paid_amount',
        'payment_notes',
        
        'razorpay_payment_id',
        'razorpay_payment_status',
        'razorpay_payment_method',
        'razorpay_amount',
        'razorpay_checked_at',
        'payment_gateway',
    
    ];

    protected $casts = [
        'order_date' => 'date',
        'dye_received_date' => 'date',
        'print_received_date' => 'date',
        'emb_received_date' => 'date',
        'master_received_date' => 'date',
        'dispatched_date' => 'datetime',
        'delivered_date' => 'datetime',
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'razorpay_checked_at' => 'datetime',
    ];

    protected $appends = [
        'order_status_badge',
        'payment_status_badge',
        'shipping_status_badge',
    ];
    
    
    // Add accessor for Razorpay status badge
    public function getRazorpayStatusBadgeAttribute()
    {
        if (!$this->razorpay_payment_status) {
            return 'secondary'; // Not checked
        }

        $badges = [
            'captured' => 'success',
            'authorized' => 'success',
            'created' => 'warning',
            'failed' => 'danger',
            'refunded' => 'info',
        ];

        return $badges[$this->razorpay_payment_status] ?? 'secondary';
    }

    // Add accessor for gateway badge
    public function getPaymentGatewayBadgeAttribute()
    {
        $badges = [
            'razorpay' => 'bg-purple-100 text-purple-800',
            'cod' => 'bg-yellow-100 text-yellow-800',
            'bank_transfer' => 'bg-blue-100 text-blue-800',
            'cheque' => 'bg-gray-100 text-gray-800',
        ];

        return $badges[$this->payment_gateway] ?? 'bg-gray-100 text-gray-800';
    }

    /**
     * RELATIONSHIPS
     */

    /**
     * Get tracking events
     */
    public function trackingEvents()
    {
        return $this->hasMany(\App\Models\ShippingTracking::class, 'order_id')
               ->orderBy('tracked_at', 'desc');
    }

    public function products()
    {
        return $this->hasMany(OrderProduct::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
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

    public function shippingPartner()
    {
        return $this->belongsTo(ShippingPartner::class);
    }

    public function statusHistory()
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function shippingTracking()
    {
        return $this->hasMany(ShippingTracking::class);
    }

    public function woocommerceSyncLogs()
    {
        return $this->hasMany(WooCommerceSyncLog::class);
    }

    /**
     * ACCESSORS (Getters)
     */

    public function getOrderStatusBadgeAttribute()
    {
        $badges = [
            'new' => 'primary',
            'processing' => 'info',
            'dispatched' => 'warning',
            'delivered' => 'success',
            'cancelled' => 'danger',
        ];

        return $badges[$this->order_status] ?? 'secondary';
    }

    public function getPaymentStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'warning',
            'partial' => 'info',
            'received' => 'success',
            'remittance_balance' => 'danger',
        ];

        return $badges[$this->payment_status] ?? 'secondary';
    }

    public function getShippingStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'secondary',
            'dispatched' => 'primary',
            'in_transit' => 'info',
            'out_for_delivery' => 'warning',
            'delivered' => 'success',
            'failed' => 'danger',
        ];

        return $badges[$this->shipping_status] ?? 'secondary';
    }

    public function getDyeStatusBadgeAttribute()
    {
        return $this->getStatusBadge($this->dye_status);
    }

    public function getPrintStatusBadgeAttribute()
    {
        return $this->getStatusBadge($this->print_status);
    }

    public function getEmbStatusBadgeAttribute()
    {
        return $this->getStatusBadge($this->emb_status);
    }

    public function getMasterStatusBadgeAttribute()
    {
        return $this->getStatusBadge($this->master_status);
    }

    protected function getStatusBadge($status)
    {
        $badges = [
            'pending' => 'warning',
            'received' => 'info',
            'completed' => 'success',
            'na' => 'secondary',
        ];

        return $badges[$status] ?? 'secondary';
    }

    public function getBalanceAmountAttribute()
    {
        return $this->amount - $this->paid_amount;
    }

    public function getPaymentPercentageAttribute()
    {
        if ($this->amount == 0) return 0;
        return ($this->paid_amount / $this->amount) * 100;
    }

    public function getProductImageUrlAttribute()
    {
        if ($this->product_image) {
            return str_starts_with($this->product_image, 'http')
                ? $this->product_image
                : asset('storage/' . $this->product_image);
        }
        return asset('images/no-image.png');
    }

    public function getOrderStatusLabelAttribute()
    {
        $labels = [
            'new' => 'New Order',
            'processing' => 'Processing',
            'dispatched' => 'Dispatched',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
        ];

        return $labels[$this->order_status] ?? 'Unknown';
    }

    public function getPaymentStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'Pending',
            'partial' => 'Partial Payment',
            'received' => 'Payment Received',
            'remittance_balance' => 'Remittance Balance',
        ];

        return $labels[$this->payment_status] ?? 'Unknown';
    }

    public function getShippingStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'Pending',
            'dispatched' => 'Dispatched',
            'in_transit' => 'In Transit',
            'out_for_delivery' => 'Out for Delivery',
            'delivered' => 'Delivered',
            'failed' => 'Failed',
        ];

        return $labels[$this->shipping_status] ?? 'Unknown';
    }

    public function getIsReadyToDispatchAttribute()
    {
        return $this->master_status === 'completed' && 
               $this->shipping_status === 'pending';
    }

    public function getIsCompletedAttribute()
    {
        return $this->order_status === 'delivered';
    }

    public function getWorkflowProgressAttribute()
    {
        $stages = [
            $this->dye_status,
            $this->print_status,
            $this->emb_status,
            $this->master_status,
        ];

        // Each stage contributes an equal share of 100%. "completed"/"na" counts
        // as fully done; "received" (in progress - at least one product has
        // moved past pending for this stage) counts as half credit instead of
        // zero, so the bar reflects real progress rather than jumping in big
        // steps only when a whole stage is fully finished.
        $totalPoints = 0;
        foreach ($stages as $status) {
            if ($status === 'completed' || $status === 'na') {
                $totalPoints += 1;
            } elseif ($status === 'received' || $status === 'in_progress') {
                $totalPoints += 0.5;
            }
            // 'pending' or null contributes 0
        }

        return round(($totalPoints / count($stages)) * 100);
    }

    /**
     * Roll up each product's per-stage status onto the order's own
     * dye_status/print_status/emb_status/master_status columns.
     *
     * The dashboard's "Workflow Stages" widget (and workflow_progress above)
     * read these order-level columns, but day-to-day workflow updates happen
     * per-product on order_products. Without this sync the order-level
     * columns stay frozen at their default and the workflow display never
     * reflects real progress.
     */
    public function syncStageStatusesFromProducts()
    {
        $this->loadMissing('products');
        $products = $this->products;

        if ($products->isEmpty()) {
            return;
        }

        $stages = ['dye', 'print', 'emb', 'master'];
        $updates = [];

        foreach ($stages as $stage) {
            $field = $stage . '_status';
            $statuses = $products->pluck($field)->filter()->values();

            if ($statuses->isEmpty()) {
                continue;
            }

            if ($statuses->every(fn($s) => in_array($s, ['completed', 'na']))) {
                $updates[$field] = $statuses->contains('completed') ? 'completed' : 'na';
            } elseif ($statuses->contains('completed') || $statuses->contains('in_progress') || $statuses->contains('received')) {
                $updates[$field] = 'received';
            } else {
                $updates[$field] = 'pending';
            }
        }

        if (!empty($updates)) {
            $this->update($updates);
        }
    }

    /**
     * SCOPES (Query Filters)
     */

    public function scopeNew($query)
    {
        return $query->where('order_status', 'new');
    }

    public function scopeProcessing($query)
    {
        return $query->where('order_status', 'processing');
    }

    public function scopeDispatched($query)
    {
        return $query->where('order_status', 'dispatched');
    }

    public function scopeDelivered($query)
    {
        return $query->where('order_status', 'delivered');
    }

    public function scopeReadyToDispatch($query)
    {
        return $query->where('master_status', 'completed')
                     ->where('shipping_status', 'pending');
    }

    public function scopePendingPayment($query)
    {
        return $query->whereIn('payment_status', ['pending', 'partial']);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('order_date', [$startDate, $endDate]);
    }

    public function scopeByCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeByVendor($query, $vendorId, $type)
    {
        $field = $type . '_vendor_id';
        return $query->where($field, $vendorId);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('order_id', 'like', "%{$search}%")
              ->orWhere('awb_number', 'like', "%{$search}%")
              ->orWhereHas('customer', function($q) use ($search) {
                  $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
              });
        });
    }

    /**
     * MUTATORS (Setters)
     */

    public function setOrderIdAttribute($value)
    {
        $this->attributes['order_id'] = strtoupper($value);
    }

    /**
     * ✅ REMOVED: Boot method no longer auto-generates order_id
     * All order ID generation now happens EXPLICITLY in controllers/services
     * with DB::beginTransaction() and lockForUpdate() to prevent duplicates
     */
    
}

// namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\SoftDeletes;

// class Order extends Model
// {
//     use HasFactory, SoftDeletes;

//     protected $fillable = [
//         'order_id',
//         'customer_id',
//         'order_date',
//         'amount',
        
//         // WooCommerce Integration
//         'woocommerce_order_id',
//         'woocommerce_raw_data',
        
//         // Product Details
//         'product_image',
//         'product_description',
        
//         // Workflow Stages - Dye
//         'dye_vendor_id',
//         'dye_status',
//         'dye_received_date',
        
//         // Workflow Stages - Print
//         'print_vendor_id',
//         'print_status',
//         'print_received_date',
        
//         // Workflow Stages - Embroidery
//         'emb_vendor_id',
//         'emb_status',
//         'emb_received_date',
        
//         // Workflow Stages - Master
//         'master_vendor_id',
//         'master_status',
//         'master_received_date',
        
//         // Shipping Details
//         'shipping_partner_id',
//         'awb_number',
//         'dispatched_date',
//         'shipping_status',
//         'delivered_date',
        
//         // Overall Status
//         'order_status',
//          'remark',
//         // Payment
//         'payment_status',
//         'paid_amount',
//         'payment_notes',
        
//         'razorpay_payment_id',
//         'razorpay_payment_status',
//         'razorpay_payment_method',
//         'razorpay_amount',
//         'razorpay_checked_at',
//         'payment_gateway',
    
//     ];

//   protected $casts = [
//     'order_date' => 'date',
//     'dye_received_date' => 'date',
//     'print_received_date' => 'date',
//     'emb_received_date' => 'date',
//     'master_received_date' => 'date',
//     'dispatched_date' => 'datetime',
//     'delivered_date' => 'datetime',
//     'amount' => 'decimal:2',
//     'paid_amount' => 'decimal:2',
//     'razorpay_checked_at' => 'datetime',
// ];

//     protected $appends = [
//         'order_status_badge',
//         'payment_status_badge',
//         'shipping_status_badge',
//     ];
    
    
// // Add accessor for Razorpay status badge
// public function getRazorpayStatusBadgeAttribute()
// {
//     if (!$this->razorpay_payment_status) {
//         return 'secondary'; // Not checked
//     }

//     $badges = [
//         'captured' => 'success',
//         'authorized' => 'success',
//         'created' => 'warning',
//         'failed' => 'danger',
//         'refunded' => 'info',
//     ];

//     return $badges[$this->razorpay_payment_status] ?? 'secondary';
// }

// // Add accessor for gateway badge
// public function getPaymentGatewayBadgeAttribute()
// {
//     $badges = [
//         'razorpay' => 'bg-purple-100 text-purple-800',
//         'cod' => 'bg-yellow-100 text-yellow-800',
//         'bank_transfer' => 'bg-blue-100 text-blue-800',
//         'cheque' => 'bg-gray-100 text-gray-800',
//     ];

//     return $badges[$this->payment_gateway] ?? 'bg-gray-100 text-gray-800';
// }
//     /**
//      * RELATIONSHIPS
//      */
                    
//         /**
//          * Get tracking events
//          */
//         public function trackingEvents()
//         {
//             return $this->hasMany(\App\Models\ShippingTracking::class, 'order_id')
//                   ->orderBy('tracked_at', 'desc');
//         }   
//         public function products()
// {
//     return $this->hasMany(OrderProduct::class);
// }
//     public function customer()
//     {
//         return $this->belongsTo(Customer::class);
//     }

//     public function dyeVendor()
//     {
//         return $this->belongsTo(Vendor::class, 'dye_vendor_id');
//     }

//     public function printVendor()
//     {
//         return $this->belongsTo(Vendor::class, 'print_vendor_id');
//     }

//     public function embVendor()
//     {
//         return $this->belongsTo(Vendor::class, 'emb_vendor_id');
//     }

//     public function masterVendor()
//     {
//         return $this->belongsTo(Vendor::class, 'master_vendor_id');
//     }

//     public function shippingPartner()
//     {
//         return $this->belongsTo(ShippingPartner::class);
//     }

//     public function statusHistory()
//     {
//         return $this->hasMany(OrderStatusHistory::class);
//     }

//     public function shippingTracking()
//     {
//         return $this->hasMany(ShippingTracking::class);
//     }

//     public function woocommerceSyncLogs()
//     {
//         return $this->hasMany(WooCommerceSyncLog::class);
//     }

//     /**
//      * ACCESSORS (Getters)
//      */

//     public function getOrderStatusBadgeAttribute()
//     {
//         $badges = [
//             'new' => 'primary',
//             'processing' => 'info',
//             'dispatched' => 'warning',
//             'delivered' => 'success',
//             'cancelled' => 'danger',
//         ];

//         return $badges[$this->order_status] ?? 'secondary';
//     }

//     public function getPaymentStatusBadgeAttribute()
//     {
//         $badges = [
//             'pending' => 'warning',
//             'partial' => 'info',
//             'received' => 'success',
//             'remittance_balance' => 'danger',
//         ];

//         return $badges[$this->payment_status] ?? 'secondary';
//     }

//     public function getShippingStatusBadgeAttribute()
//     {
//         $badges = [
//             'pending' => 'secondary',
//             'dispatched' => 'primary',
//             'in_transit' => 'info',
//             'out_for_delivery' => 'warning',
//             'delivered' => 'success',
//             'failed' => 'danger',
//         ];

//         return $badges[$this->shipping_status] ?? 'secondary';
//     }

//     public function getDyeStatusBadgeAttribute()
//     {
//         return $this->getStatusBadge($this->dye_status);
//     }

//     public function getPrintStatusBadgeAttribute()
//     {
//         return $this->getStatusBadge($this->print_status);
//     }

//     public function getEmbStatusBadgeAttribute()
//     {
//         return $this->getStatusBadge($this->emb_status);
//     }

//     public function getMasterStatusBadgeAttribute()
//     {
//         return $this->getStatusBadge($this->master_status);
//     }

//     protected function getStatusBadge($status)
//     {
//         $badges = [
//             'pending' => 'warning',
//             'received' => 'info',
//             'completed' => 'success',
//             'na' => 'secondary',
//         ];

//         return $badges[$status] ?? 'secondary';
//     }

//     public function getBalanceAmountAttribute()
//     {
//         return $this->amount - $this->paid_amount;
//     }

//     public function getPaymentPercentageAttribute()
//     {
//         if ($this->amount == 0) return 0;
//         return ($this->paid_amount / $this->amount) * 100;
//     }

//     public function getProductImageUrlAttribute()
//     {
//         if ($this->product_image) {
//             return asset('storage/' . $this->product_image);
//         }
//         return asset('images/no-image.png');
//     }

//     public function getOrderStatusLabelAttribute()
//     {
//         $labels = [
//             'new' => 'New Order',
//             'processing' => 'Processing',
//             'dispatched' => 'Dispatched',
//             'delivered' => 'Delivered',
//             'cancelled' => 'Cancelled',
//         ];

//         return $labels[$this->order_status] ?? 'Unknown';
//     }

//     public function getPaymentStatusLabelAttribute()
//     {
//         $labels = [
//             'pending' => 'Pending',
//             'partial' => 'Partial Payment',
//             'received' => 'Payment Received',
//             'remittance_balance' => 'Remittance Balance',
//         ];

//         return $labels[$this->payment_status] ?? 'Unknown';
//     }

//     public function getShippingStatusLabelAttribute()
//     {
//         $labels = [
//             'pending' => 'Pending',
//             'dispatched' => 'Dispatched',
//             'in_transit' => 'In Transit',
//             'out_for_delivery' => 'Out for Delivery',
//             'delivered' => 'Delivered',
//             'failed' => 'Failed',
//         ];

//         return $labels[$this->shipping_status] ?? 'Unknown';
//     }

//     public function getIsReadyToDispatchAttribute()
//     {
//         return $this->master_status === 'completed' && 
//               $this->shipping_status === 'pending';
//     }

//     public function getIsCompletedAttribute()
//     {
//         return $this->order_status === 'delivered';
//     }

//   public function getWorkflowProgressAttribute()
// {
//     $stages = [
//         $this->dye_status,
//         $this->print_status,
//         $this->emb_status,
//         $this->master_status,
//     ];

//     // Each stage contributes an equal share of 100%. "completed"/"na" counts
//     // as fully done; "received" (in progress - at least one product has
//     // moved past pending for this stage) counts as half credit instead of
//     // zero, so the bar reflects real progress rather than jumping in big
//     // steps only when a whole stage is fully finished.
//     $totalPoints = 0;
//     foreach ($stages as $status) {
//         if ($status === 'completed' || $status === 'na') {
//             $totalPoints += 1;
//         } elseif ($status === 'received' || $status === 'in_progress') {
//             $totalPoints += 0.5;
//         }
//         // 'pending' or null contributes 0
//     }

//     return round(($totalPoints / count($stages)) * 100);
// }
// /**
//  * Roll up each product's per-stage status onto the order's own
//  * dye_status/print_status/emb_status/master_status columns.
//  *
//  * The dashboard's "Workflow Stages" widget (and workflow_progress above)
//  * read these order-level columns, but day-to-day workflow updates happen
//  * per-product on order_products. Without this sync the order-level
//  * columns stay frozen at their default and the workflow display never
//  * reflects real progress.
//  */
// public function syncStageStatusesFromProducts()
// {
//     $this->loadMissing('products');
//     $products = $this->products;

//     if ($products->isEmpty()) {
//         return;
//     }

//     $stages = ['dye', 'print', 'emb', 'master'];
//     $updates = [];

//     foreach ($stages as $stage) {
//         $field = $stage . '_status';
//         $statuses = $products->pluck($field)->filter()->values();

//         if ($statuses->isEmpty()) {
//             continue;
//         }

//         if ($statuses->every(fn($s) => in_array($s, ['completed', 'na']))) {
//             $updates[$field] = $statuses->contains('completed') ? 'completed' : 'na';
//         } elseif ($statuses->contains('completed') || $statuses->contains('in_progress') || $statuses->contains('received')) {
//             $updates[$field] = 'received';
//         } else {
//             $updates[$field] = 'pending';
//         }
//     }

//     if (!empty($updates)) {
//         $this->update($updates);
//     }
// }
//     /**
//      * SCOPES (Query Filters)
//      */

//     public function scopeNew($query)
//     {
//         return $query->where('order_status', 'new');
//     }

//     public function scopeProcessing($query)
//     {
//         return $query->where('order_status', 'processing');
//     }

//     public function scopeDispatched($query)
//     {
//         return $query->where('order_status', 'dispatched');
//     }

//     public function scopeDelivered($query)
//     {
//         return $query->where('order_status', 'delivered');
//     }

//     public function scopeReadyToDispatch($query)
//     {
//         return $query->where('master_status', 'completed')
//                      ->where('shipping_status', 'pending');
//     }

//     public function scopePendingPayment($query)
//     {
//         return $query->whereIn('payment_status', ['pending', 'partial']);
//     }

//     public function scopeByDateRange($query, $startDate, $endDate)
//     {
//         return $query->whereBetween('order_date', [$startDate, $endDate]);
//     }

//     public function scopeByCustomer($query, $customerId)
//     {
//         return $query->where('customer_id', $customerId);
//     }

//     public function scopeByVendor($query, $vendorId, $type)
//     {
//         $field = $type . '_vendor_id';
//         return $query->where($field, $vendorId);
//     }

//     public function scopeSearch($query, $search)
//     {
//         return $query->where(function($q) use ($search) {
//             $q->where('order_id', 'like', "%{$search}%")
//               ->orWhere('awb_number', 'like', "%{$search}%")
//               ->orWhereHas('customer', function($q) use ($search) {
//                   $q->where('name', 'like', "%{$search}%")
//                     ->orWhere('phone', 'like', "%{$search}%");
//               });
//         });
//     }

//     /**
//      * MUTATORS (Setters)
//      */

//     public function setOrderIdAttribute($value)
//     {
//         $this->attributes['order_id'] = strtoupper($value);
//     }

//     /**
//      * ✅ REMOVED: Boot method no longer auto-generates order_id
//      * All order ID generation now happens EXPLICITLY in controllers/services
//      * with DB::beginTransaction() and lockForUpdate() to prevent duplicates
//      */
    
// }