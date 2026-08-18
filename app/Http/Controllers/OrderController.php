<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Customer;
use App\Models\Vendor;
use App\Models\ShippingPartner;
use App\Models\OrderStatusHistory;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\DelhiveryService;
use App\Services\RazorpayService;

class OrderController extends Controller
{
    /**
     * Display orders list
     */
    public function index(Request $request)
    {
        $query = Order::with(['customer', 'dyeVendor', 'printVendor', 'embVendor', 'masterVendor', 'shippingPartner', 'products']);
        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            // Remove # if user typed it
            $searchTerm = str_replace('#', '', $search);
            
            $query->where(function($q) use ($search, $searchTerm) {
                $q->where('order_id', 'like', "%{$searchTerm}%")
                  ->orWhere('order_id', 'like', "%#{$searchTerm}%")
                  ->orWhere('woocommerce_order_id', 'like', "%{$search}%")           
                  ->orWhere('awb_number', 'like', "%{$search}%")
                  ->orWhere('product_description', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Order Status Filter (handle both 'status' and 'order_status')
        if ($request->filled('status')) {
            $query->where('order_status', $request->status);
        }
        
        if ($request->filled('order_status')) {
            $query->where('order_status', $request->order_status);
        }

        // Payment Status Filter
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Shipping Status Filter
        if ($request->filled('shipping_status')) {
            $query->where('shipping_status', $request->shipping_status);
        }

        // Source Filter (WooCommerce vs Manual)
        if ($request->filled('source')) {
            if ($request->source == 'woocommerce') {
                $query->whereNotNull('woocommerce_order_id');
            } elseif ($request->source == 'manual') {
                $query->whereNull('woocommerce_order_id');
            }
        }

        // Workflow Stage Filter
        if ($request->filled('stage')) {
            switch ($request->stage) {
                case 'dye':
                    $query->where('dye_status', 'pending');
                    break;
                case 'print':
                    $query->where('print_status', 'pending');
                    break;
                case 'emb':
                    $query->where('emb_status', 'pending');
                    break;
                case 'master':
                    $query->where('master_status', 'pending');
                    break;
                case 'shipping':
                    $query->where('master_status', 'completed')
                          ->whereNull('awb_number');
                    break;
            }
        }

        // Date Range Filters
        if ($request->filled('date_from')) {
            $query->whereDate('order_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('order_date', '<=', $request->date_to);
        }

        // IMPORTANT: Add withQueryString() to preserve search/filters in pagination
        $orders = $query->orderBy('woocommerce_order_id', 'desc')->paginate(20)->withQueryString();
        
        // For filters dropdown
        $vendors = Vendor::where('is_active', true)->get()->groupBy('type');
        $shippingPartners = ShippingPartner::where('is_active', true)->get();

        return view('orders.index', compact('orders', 'vendors', 'shippingPartners'));
    }

    /**
     * Show create order form
     */
    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $vendors = Vendor::where('is_active', true)->get()->groupBy('type');
        $shippingPartners = ShippingPartner::where('is_active', true)->get();

        return view('orders.create', compact('customers', 'vendors', 'shippingPartners'));
    }

    /**
     * Check Razorpay payment for all pending orders
     */
    public function checkAllPayments()
    {
        $razorpayService = app(RazorpayService::class);

        if (!$razorpayService->isConfigured()) {
            return back()->with('error', 'Razorpay not configured.');
        }

        $orders = Order::whereIn('payment_status', ['pending', 'partial'])
            ->whereNotNull('woocommerce_order_id')
            ->limit(50)
            ->get();

        $checked = 0;
        $updated = 0;

        foreach ($orders as $order) {
            $result = $razorpayService->checkPaymentStatus($order);
            $checked++;
            if ($result['success']) {
                $updated++;
            }
            
            // Sleep to avoid rate limiting
            usleep(500000); // 0.5 seconds
        }

        return back()->with('success', "✅ Checked {$checked} orders, updated {$updated} payment statuses!");
    }

    /**
     * Check Razorpay payment status for order
     */
    public function checkPayment(Order $order)
    {
        $razorpayService = app(RazorpayService::class);

        if (!$razorpayService->isConfigured()) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Razorpay not configured'
                ]);
            }
            return back()->with('error', 'Razorpay not configured. Add credentials to .env file.');
        }

        // If payment notes has Razorpay ID, fetch directly
        if ($order->payment_notes && str_contains($order->payment_notes, 'pay_')) {
            preg_match('/pay_[a-zA-Z0-9]+/', $order->payment_notes, $matches);
            if (!empty($matches[0])) {
                $result = $razorpayService->fetchPayment($matches[0]);
                if ($result['success']) {
                    // Update order
                    $paymentStatus = $result['status'] === 'captured' ? 'received' : 'pending';
                    $order->update([
                        'payment_status' => $paymentStatus,
                        'paid_amount' => $result['status'] === 'captured' ? $result['amount'] : $order->paid_amount,
                        'razorpay_payment_status' => $result['status'],
                        'razorpay_amount' => $result['amount'],
                        'razorpay_payment_method' => $result['method'],
                        'razorpay_checked_at' => now(),
                    ]);
                    
                    if (request()->ajax()) {
                        return response()->json([
                            'success' => true,
                            'message' => "✅ Payment {$result['status']}!",
                            'razorpay_status' => $result['status'],
                            'payment_status' => $paymentStatus,
                            'amount' => $result['amount'],
                            'payment_method' => $result['method'],
                        ]);
                    }
                    
                    return back()->with('success', "✅ Payment {$result['status']}! Amount: ₹{$result['amount']}");
                }
            }
        }

        // Otherwise search by order
        $result = $razorpayService->checkPaymentStatus($order);

        if ($result['success']) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "✅ Payment {$result['status']}!",
                    'razorpay_status' => $result['status'],
                    'payment_status' => $result['crm_status'],
                    'amount' => $result['amount'],
                    'payment_method' => $result['method'],
                ]);
            }
            
            return back()->with('success', "✅ Payment {$result['status']}! Razorpay ID: {$result['payment_id']}");
        }

        if (request()->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ]);
        }

        return back()->with('error', '❌ ' . $result['message']);
    }

    /**
     * Store new order
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'order_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'product_image' => 'nullable|image|max:5120',
            'product_description' => 'nullable|string',
            
            'dye_vendor_id' => 'nullable|exists:vendors,id',
            'dye_status' => 'required|in:pending,received,completed,na',
            'dye_received_date' => 'nullable|date',
            
            'print_vendor_id' => 'nullable|exists:vendors,id',
            'print_status' => 'required|in:pending,received,completed,na',
            'print_received_date' => 'nullable|date',
            
            'emb_vendor_id' => 'nullable|exists:vendors,id',
            'emb_status' => 'required|in:pending,received,completed,na',
            'emb_received_date' => 'nullable|date',
            
            'master_vendor_id' => 'nullable|exists:vendors,id',
            'master_status' => 'required|in:pending,received,completed',
            'master_received_date' => 'nullable|date',
            
            'payment_status' => 'required|in:pending,partial,received,remittance_balance',
            'paid_amount' => 'nullable|numeric|min:0',
            'payment_notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // ✅ LOCK FOR UPDATE - Prevent concurrent ID duplication
            $lastOrder = Order::lockForUpdate()->latest('id')->first();
            $orderNumber = $lastOrder ? (int) str_replace('#', '', $lastOrder->order_id) + 1 : 4134;
            $validated['order_id'] = '#' . $orderNumber;

            // Handle product image upload
            if ($request->hasFile('product_image')) {
                $validated['product_image'] = $request->file('product_image')->store('products', 'public');
            }

            // Determine overall order status
            $validated['order_status'] = $this->determineOrderStatus($validated);

            // Create order
            $order = Order::create($validated);

            // Create status history
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'stage' => 'order',
                'new_status' => 'created',
                'notes' => 'Order created',
                'updated_by' => auth()->id(),
            ]);
            
            // ✅ LOG ORDER CREATION
            ActivityLogService::log(
                'created',
                'orders',
                $order->id,
                'Order',
                "Created order: #{$order->order_id} from {$order->customer->name}"
            );

            DB::commit();

            return redirect()->route('orders.show', $order)
                ->with('success', 'Order created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Log the error
            Log::error('Order creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Activity log for failure
            ActivityLogService::log(
                'error',
                'orders',
                'failed_create_' . now()->timestamp,
                'Order',
                "Failed to create order: " . $e->getMessage()
            );

            return back()
                ->withInput()
                ->with('error', 'Error creating order: ' . $e->getMessage());
        }
    }

    /**
     * Display order details
     */
    public function show(Order $order)
    {
       
        $order->load([
            'customer',
            'dyeVendor',
            'printVendor',
            'embVendor',
            'masterVendor',
            'shippingPartner',
            'statusHistory.updatedBy',
            'shippingTracking',
            'products.dyeVendor',
            'products.printVendor',
            'products.embVendor',
            'products.masterVendor',
        ]);

        $vendors = Vendor::where('is_active', true)->get()->groupBy('type');
        $shippingPartners = ShippingPartner::where('is_active', true)->get();

        return view('orders.show', compact('order', 'vendors', 'shippingPartners'));
    }

    /**
     * Show edit form
     */
    public function edit(Request $request, Order $order)
    {
        // Load products with their vendors
        $order->load([
            'products.dyeVendor',
            'products.printVendor',
            'products.embVendor',
            'products.masterVendor'
        ]);
        
        $customers = Customer::orderBy('name')->get();
        $vendors = Vendor::where('is_active', true)->get()->groupBy('type');
        $shippingPartners = ShippingPartner::where('is_active', true)->get();

        // Preserve the page/filters the user came from so "Back to Orders" can return them there
        $backParams = $request->only([
            'page', 'search', 'status', 'order_status', 'payment_status',
            'shipping_status', 'source', 'stage', 'date_from', 'date_to',
        ]);
        
        return view('orders.edit', compact('order', 'customers', 'vendors', 'shippingPartners', 'backParams'));
    }

    /**
     * Update order
     */
    public function update(Request $request, Order $order)
    {
        // ✅ CAPTURE OLD DATA BEFORE UPDATE
        $oldData = $order->toArray();

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'order_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'payment_status' => 'required|in:pending,partial,received,remittance_balance',
            'payment_notes' => 'nullable|string',
            'payment_gateway' => 'required|in:razorpay,cod,bank_transfer,cheque,other',
            'order_status' => 'required|in:new,processing,dispatched,delivered,cancelled,refunded,failed,rto_pay_pending,exchange_received,exchange_completed,refund_requested,refund_approved,refund_cancelled',
            'product_description' => 'nullable|string',
            'remark' => 'nullable|string',
            'shipping_partner_id' => 'nullable|exists:shipping_partners,id',
            'awb_number' => 'nullable|string',
            'shipping_status' => 'nullable|in:pending,dispatched,in_transit,out_for_delivery,delivered',
            
            // Product workflow validation
            'products' => 'sometimes|array',
            'products.*.id' => 'sometimes|required|exists:order_products,id',
            'products.*.dye_status' => 'sometimes|required|in:pending,in_progress,completed',
            'products.*.dye_vendor_id' => 'nullable|exists:vendors,id',
            'products.*.dye_received_date' => 'nullable|date',
            'products.*.print_status' => 'sometimes|required|in:pending,in_progress,completed',
            'products.*.print_vendor_id' => 'nullable|exists:vendors,id',
            'products.*.print_received_date' => 'nullable|date',
            'products.*.emb_status' => 'sometimes|required|in:pending,in_progress,completed',
            'products.*.emb_vendor_id' => 'nullable|exists:vendors,id',
            'products.*.emb_received_date' => 'nullable|date',
            'products.*.master_status' => 'sometimes|required|in:pending,in_progress,completed',
            'products.*.master_vendor_id' => 'nullable|exists:vendors,id',
            'products.*.master_received_date' => 'nullable|date',
        ]);

        // Update order basic info
        $order->update([
            'customer_id' => $validated['customer_id'],
            'order_date' => $validated['order_date'],
            'amount' => $validated['amount'],
            'paid_amount' => $validated['paid_amount'] ?? 0,
            'payment_status' => $validated['payment_status'],
            'payment_notes' => $validated['payment_notes'] ?? null,
            'payment_gateway' => $validated['payment_gateway'],
            'order_status' => $validated['order_status'],
            'product_description' => $validated['product_description'] ?? null,
            'remark' => $validated['remark'] ?? null,
            'shipping_partner_id' => $validated['shipping_partner_id'] ?? null,
            'awb_number' => $validated['awb_number'] ?? null,
            'shipping_status' => $validated['shipping_status'] ?? 'pending',
        ]);

        // Update each product's workflow
        if (isset($validated['products']) && is_array($validated['products'])) {
            foreach ($validated['products'] as $productData) {
                OrderProduct::where('id', $productData['id'])
                    ->where('order_id', $order->id) // Security check
                    ->update([
                        'dye_status' => $productData['dye_status'],
                        'dye_vendor_id' => $productData['dye_vendor_id'] ?? null,
                        'dye_received_date' => $productData['dye_received_date'] ?? null,
                        'print_status' => $productData['print_status'],
                        'print_vendor_id' => $productData['print_vendor_id'] ?? null,
                        'print_received_date' => $productData['print_received_date'] ?? null,
                        'emb_status' => $productData['emb_status'],
                        'emb_vendor_id' => $productData['emb_vendor_id'] ?? null,
                        'emb_received_date' => $productData['emb_received_date'] ?? null,
                        'master_status' => $productData['master_status'],
                        'master_vendor_id' => $productData['master_vendor_id'] ?? null,
                        'master_received_date' => $productData['master_received_date'] ?? null,
                    ]);
            }

            // Roll the per-product statuses up onto the order itself so the
            // dashboard's Workflow Stages widget reflects real progress.
            $order->syncStageStatusesFromProducts();
        }

        // ✅ LOG ORDER UPDATE
        ActivityLogService::log(
            'edited',
            'orders',
            $order->id,
            'Order',
            "Updated order: #{$order->order_id}",
            $oldData,
            $order->toArray()
        );

        return redirect()->route('orders.show', $order)
            ->with('success', 'Order updated successfully!');
    }

    /**
     * Delete order
     */
    public function destroy(Order $order)
    {
        // ✅ CAPTURE ORDER DATA BEFORE DELETION
        $orderNumber = $order->order_id;
        $customerName = $order->customer->name ?? 'Unknown';

        // Delete product image
        if ($order->product_image) {
            Storage::disk('public')->delete($order->product_image);
        }

        // ✅ LOG ORDER DELETION
        ActivityLogService::log(
            'deleted',
            'orders',
            $order->id,
            'Order',
            "Deleted order: #{$orderNumber} from {$customerName}"
        );

        $order->delete();

        return redirect()->route('orders.index')
            ->with('success', 'Order deleted successfully!');
    }

    /**
     * Quick update stage status
     */
    public function updateStage(Request $request, Order $order)
    {
        $validated = $request->validate([
            'stage' => 'required|in:dye,print,emb,master,shipping',
            'status' => 'required|string',
            'vendor_id' => 'nullable|exists:vendors,id',
            'received_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $stage = $validated['stage'];
        $oldStatus = $order->{$stage . '_status'};

        // Update stage status
        $order->{$stage . '_status'} = $validated['status'];
        
        if (isset($validated['vendor_id'])) {
            $order->{$stage . '_vendor_id'} = $validated['vendor_id'];
        }
        
        if (isset($validated['received_date'])) {
            $order->{$stage . '_received_date'} = $validated['received_date'];
        }

        // Update overall order status
        $order->order_status = $this->determineOrderStatus($order->toArray());
        
        $order->save();

        // Log status change
        OrderStatusHistory::create([
            'order_id' => $order->id,
            'stage' => $stage,
            'old_status' => $oldStatus,
            'new_status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
            'updated_by' => auth()->id(),
        ]);

        // ✅ LOG STAGE UPDATE
        ActivityLogService::log(
            'edited',
            'orders',
            $order->id,
            'Order',
            "Changed {$stage} stage from {$oldStatus} to {$validated['status']}"
        );

        return response()->json([
            'success' => true,
            'message' => ucfirst($stage) . ' stage updated successfully!',
            'order_status' => $order->order_status,
        ]);
    }

    /**
     * Update shipping details
     */
    public function updateShipping(Request $request, Order $order)
    {
        $validated = $request->validate([
            'shipping_partner_id' => 'required|exists:shipping_partners,id',
            'awb_number' => 'required|string',
            'dispatched_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $oldStatus = $order->shipping_status;
        $oldAWB = $order->awb_number;

        $order->update([
            'shipping_partner_id' => $validated['shipping_partner_id'],
            'awb_number' => $validated['awb_number'],
            'dispatched_date' => $validated['dispatched_date'],
            'shipping_status' => 'dispatched',
            'order_status' => 'dispatched',
        ]);

        // Log status change
        OrderStatusHistory::create([
            'order_id' => $order->id,
            'stage' => 'shipping',
            'old_status' => $oldStatus,
            'new_status' => 'dispatched',
            'notes' => $validated['notes'] ?? 'Order dispatched with AWB: ' . $validated['awb_number'],
            'updated_by' => auth()->id(),
        ]);

        // ✅ LOG SHIPPING UPDATE
        ActivityLogService::log(
            'edited',
            'orders',
            $order->id,
            'Order',
            "Updated shipping: AWB {$oldAWB} → {$validated['awb_number']}"
        );

        // Immediately pull real tracking data for the AWB that was just entered
        try {
            $delhiveryService = app(\App\Services\DelhiveryService::class);

            if ($delhiveryService->isConfigured()) {
                $result = $delhiveryService->trackShipment($order->awb_number);

                if ($result['success']) {
                    $shippingStatus = $delhiveryService->mapStatusToCRM($result['status'] ?? 'pending');
                    $updateData = ['shipping_status' => $shippingStatus];

                    if ($shippingStatus === 'delivered') {
                        $deliveredAt = null;

                        if (!empty($result['scans']) && is_array($result['scans'])) {
                            foreach ($result['scans'] as $scan) {
                                if (empty($scan['date_time']) || empty($scan['status'])) {
                                    continue;
                                }
                                if (stripos($scan['status'], 'delivered') === false) {
                                    continue;
                                }
                                try {
                                    $scanDate = \Carbon\Carbon::parse($scan['date_time']);
                                } catch (\Exception $e) {
                                    continue;
                                }
                                if (!$deliveredAt || $scanDate->lt($deliveredAt)) {
                                    $deliveredAt = $scanDate;
                                }
                            }
                        }

                        $updateData['delivered_date'] = $deliveredAt ?? now();
                        $updateData['order_status'] = 'delivered';
                    }

                    $order->update($updateData);

                    \App\Models\ShippingTracking::where('order_id', $order->id)->delete();

                    if (!empty($result['scans']) && is_array($result['scans'])) {
                        foreach ($result['scans'] as $scan) {
                            if (empty($scan['date_time'])) {
                                continue;
                            }
                            try {
                                $trackedAt = \Carbon\Carbon::parse($scan['date_time']);
                            } catch (\Exception $e) {
                                continue;
                            }
                            \App\Models\ShippingTracking::create([
                                'order_id' => $order->id,
                                'awb_number' => $order->awb_number,
                                'tracked_at' => $trackedAt,
                                'status' => $scan['status'] ?? 'Unknown',
                                'location' => $scan['location'] ?? 'Unknown',
                                'remarks' => $scan['remarks'] ?? null,
                                'raw_data' => json_encode($scan),
                            ]);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Auto-fetch tracking after manual AWB entry failed', [
                'order_id' => $order->id,
                'awb' => $order->awb_number,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Shipping details updated successfully!',
        ]);
    }

    /**
     * Update payment status
     */
    public function updatePayment(Request $request, Order $order)
    {
        $validated = $request->validate([
            'payment_status' => 'required|in:pending,partial,received,remittance_balance',
            'paid_amount' => 'nullable|numeric|min:0',
            'payment_notes' => 'nullable|string',
        ]);

        $oldStatus = $order->payment_status;
        $oldAmount = $order->paid_amount;

        $order->update($validated);

        // Log payment update
        OrderStatusHistory::create([
            'order_id' => $order->id,
            'stage' => 'payment',
            'old_status' => $order->payment_status,
            'new_status' => $validated['payment_status'],
            'notes' => $validated['payment_notes'] ?? 'Payment status updated',
            'updated_by' => auth()->id(),
        ]);

        // ✅ LOG PAYMENT UPDATE
        ActivityLogService::log(
            'edited',
            'orders',
            $order->id,
            'Order',
            "Updated payment: {$oldStatus} (₹{$oldAmount}) → {$validated['payment_status']} (₹{$validated['paid_amount']})"
        );

        return response()->json([
            'success' => true,
            'message' => 'Payment status updated successfully!',
        ]);
    }

    /**
     * Bulk update orders
     */
    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:orders,id',
            'action' => 'required|in:update_status,assign_vendor,update_payment',
            'data' => 'required|array',
        ]);

        $orders = Order::whereIn('id', $validated['order_ids'])->get();
        $updated = 0;

        foreach ($orders as $order) {
            try {
                switch ($validated['action']) {
                    case 'update_status':
                        if (isset($validated['data']['stage']) && isset($validated['data']['status'])) {
                            $stage = $validated['data']['stage'];
                            $order->{$stage . '_status'} = $validated['data']['status'];
                            $order->save();
                            $updated++;

                            // ✅ LOG BULK STATUS UPDATE
                            ActivityLogService::log(
                                'edited',
                                'orders',
                                $order->id,
                                'Order',
                                "Bulk updated: {$stage} status to {$validated['data']['status']}"
                            );
                        }
                        break;

                    case 'assign_vendor':
                        if (isset($validated['data']['stage']) && isset($validated['data']['vendor_id'])) {
                            $stage = $validated['data']['stage'];
                            $order->{$stage . '_vendor_id'} = $validated['data']['vendor_id'];
                            $order->save();
                            $updated++;

                            // ✅ LOG BULK VENDOR ASSIGNMENT
                            ActivityLogService::log(
                                'edited',
                                'orders',
                                $order->id,
                                'Order',
                                "Bulk assigned vendor for {$stage} stage"
                            );
                        }
                        break;

                    case 'update_payment':
                        if (isset($validated['data']['payment_status'])) {
                            $order->payment_status = $validated['data']['payment_status'];
                            $order->save();
                            $updated++;

                            // ✅ LOG BULK PAYMENT UPDATE
                            ActivityLogService::log(
                                'edited',
                                'orders',
                                $order->id,
                                'Order',
                                "Bulk updated payment status to {$validated['data']['payment_status']}"
                            );
                        }
                        break;
                }
            } catch (\Exception $e) {
                Log::error('Bulk update failed for order ' . $order->id . ': ' . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => "{$updated} orders updated successfully!",
        ]);
    }

    /**
     * Export orders to Excel
     */
    public function export(Request $request)
    {
        $query = Order::with(['customer', 'dyeVendor', 'printVendor', 'embVendor', 'masterVendor', 'shippingPartner']);

        // Apply same filters as index
        if ($request->filled('status')) {
            $query->where('order_status', $request->status);
        }

        $orders = $query->get();

        $filename = 'orders_' . now()->format('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($orders) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, [
                'SR.NO', 'Date', 'Order ID', 'Customer Name', 'Product Image', 
                'Dye', 'Print', 'Emb', 'Master', 'Status',
                'Shipping Partner', 'Dispatched Date', 'AWB No', 'Order Status',
                'Amount', 'Payment Status'
            ]);

            // Data
            foreach ($orders as $index => $order) {
                fputcsv($file, [
                    $index + 1,
                    $order->order_date->format('d M Y'),
                    $order->order_id,
                    $order->customer->name,
                    $order->product_image ? asset('storage/' . $order->product_image) : '',
                    $order->dyeVendor ? $order->dyeVendor->name : 'NA',
                    $order->printVendor ? $order->printVendor->name : 'NA',
                    $order->embVendor ? $order->embVendor->name : 'NA',
                    $order->masterVendor ? $order->masterVendor->name : '',
                    strtoupper($order->order_status),
                    $order->shippingPartner ? $order->shippingPartner->name : '',
                    $order->dispatched_date ? $order->dispatched_date->format('d M') : '',
                    $order->awb_number ?? '',
                    strtoupper(str_replace('_', ' ', $order->shipping_status)),
                    $order->amount,
                    strtoupper(str_replace('_', ' ', $order->payment_status)),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Determine overall order status based on stages
     */
    protected function determineOrderStatus($data)
    {
        // If delivered
        if (isset($data['shipping_status']) && $data['shipping_status'] === 'delivered') {
            return 'delivered';
        }

        // If dispatched
        if (isset($data['shipping_status']) && in_array($data['shipping_status'], ['dispatched', 'in_transit', 'out_for_delivery'])) {
            return 'dispatched';
        }

        // If any stage is in progress
        $stages = ['dye_status', 'print_status', 'emb_status', 'master_status'];
        foreach ($stages as $stage) {
            if (isset($data[$stage]) && in_array($data[$stage], ['received', 'completed'])) {
                return 'processing';
            }
        }

        return 'new';
    }

    /**
     * Log status changes for audit trail
     */
    protected function logStatusChanges($order, $newData)
    {
        $stages = [
            'dye' => 'dye_status',
            'print' => 'print_status',
            'emb' => 'emb_status',
            'master' => 'master_status',
            'shipping' => 'shipping_status',
        ];

        foreach ($stages as $stageName => $statusField) {
            if (isset($newData[$statusField]) && $order->$statusField !== $newData[$statusField]) {
                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'stage' => $stageName,
                    'old_status' => $order->$statusField,
                    'new_status' => $newData[$statusField],
                    'notes' => 'Status updated',
                    'updated_by' => auth()->id(),
                ]);
            }
        }

        // Log payment status change
        if (isset($newData['payment_status']) && $order->payment_status !== $newData['payment_status']) {
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'stage' => 'payment',
                'old_status' => $order->payment_status,
                'new_status' => $newData['payment_status'],
                'notes' => 'Payment status updated',
                'updated_by' => auth()->id(),
            ]);
        }
    }

    /**
     * Refresh tracking information from Delhivery
     */
    public function refreshTracking(Order $order)
    {
        try {
            if (!$order->awb_number) {
                return back()->with('error', 'No AWB number found for this order.');
            }

            $delhiveryService = app(\App\Services\DelhiveryService::class);

            if (!$delhiveryService->isConfigured()) {
                return back()->with('error', 'Delhivery API not configured.');
            }

            $result = $delhiveryService->trackShipment($order->awb_number);

            if ($result['success']) {
                // Update order shipping status
                $shippingStatus = $delhiveryService->mapStatusToCRM($result['status'] ?? 'pending');
                $updateData = ['shipping_status' => $shippingStatus];

                if ($shippingStatus === 'delivered') {
                    // Find the actual delivery timestamp from Delhivery's scan history
                    // instead of stamping the time the refresh button happened to be clicked.
                    $deliveredAt = null;

                    if (!empty($result['scans']) && is_array($result['scans'])) {
                        foreach ($result['scans'] as $scan) {
                            if (empty($scan['date_time']) || empty($scan['status'])) {
                                continue;
                            }
                            if (stripos($scan['status'], 'delivered') === false) {
                                continue;
                            }
                            try {
                                $scanDate = \Carbon\Carbon::parse($scan['date_time']);
                            } catch (\Exception $e) {
                                continue;
                            }
                            if (!$deliveredAt || $scanDate->lt($deliveredAt)) {
                                $deliveredAt = $scanDate;
                            }
                        }
                    }

                    if ($deliveredAt) {
                        $updateData['delivered_date'] = $deliveredAt;
                    } elseif (!$order->delivered_date) {
                        // Fallback only if we have no delivery date on record at all
                        $updateData['delivered_date'] = now();
                    }
                }
                // Note: if not delivered, we deliberately leave delivered_date untouched
                // so a stale/glitchy courier status can't wipe out a previously recorded date.

                $order->update($updateData);

                // Delete old tracking events to avoid duplicates
                \App\Models\ShippingTracking::where('order_id', $order->id)->delete();

                // Save tracking events
                if (!empty($result['scans']) && is_array($result['scans'])) {
                    foreach ($result['scans'] as $scan) {
                        // Skip if date_time is empty
                        if (empty($scan['date_time'])) {
                            continue;
                        }

                        // Parse datetime
                        try {
                            $trackedAt = \Carbon\Carbon::parse($scan['date_time']);
                        } catch (\Exception $e) {
                            \Log::warning('Invalid datetime format', [
                                'order_id' => $order->id,
                                'date_time' => $scan['date_time']
                            ]);
                            continue;
                        }

                        // Use tracked_at column
                        \App\Models\ShippingTracking::create([
                            'order_id' => $order->id,
                            'awb_number' => $order->awb_number,
                            'tracked_at' => $trackedAt,
                            'status' => $scan['status'] ?? 'Unknown',
                            'location' => $scan['location'] ?? 'Unknown',
                            'remarks' => $scan['remarks'] ?? null,
                            'raw_data' => json_encode($scan),
                        ]);
                    }
                }

                // ✅ LOG TRACKING REFRESH
                ActivityLogService::log(
                    'edited',
                    'orders',
                    $order->id,
                    'Order',
                    "Refreshed tracking for order #{$order->order_id} - Status: {$shippingStatus}"
                );

                return back()->with('success', 'Tracking information updated successfully!');
            }

            return back()->with('error', 'Failed to fetch tracking: ' . ($result['message'] ?? 'Unknown error'));

        } catch (\Exception $e) {
            \Log::error('Refresh tracking failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Auto-dispatch order with Delhivery
     */
    public function autoDispatch(Order $order)
    {
        try {
            if (in_array($order->order_status, ['delivered', 'cancelled', 'refunded'])) {
                return back()->with('error', 'Cannot dispatch an order with status: ' . $order->order_status);
            }
            // Payment check - skip for COD
            if ($order->payment_gateway !== 'cod' && $order->payment_status !== 'received') {
                return back()->with('error', 'Payment not received yet.');
            }

            // Production check - based on products
            $order->loadMissing('products');
            if ($order->products->isEmpty() || !$order->products->every(fn($p) => $p->master_status == 'completed')) {
                return back()->with('error', 'Production not completed yet.');
            }

            if ($order->awb_number) {
                return back()->with('warning', 'Order already has an AWB number.');
            }

            // Call Delhivery to create shipment
            $delhiveryService = app(\App\Services\DelhiveryService::class);
            $result = $delhiveryService->createShipment($order);

            if ($result['success']) {
                // ✅ LOG AUTO-DISPATCH
                ActivityLogService::log(
                    'edited',
                    'orders',
                    $order->id,
                    'Order',
                    "Auto-dispatched order #{$order->order_id} - AWB: {$result['awb_number']}"
                );
                return back()->with('success', 'Order dispatched successfully! AWB: ' . $result['awb_number']);
            }

            return back()->with('error', 'Dispatch failed: ' . $result['message']);

        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Cancel shipment
     */
    public function cancelShipment(Order $order)
    {
        try {
            if (!$order->awb_number) {
                return back()->with('error', 'No shipment to cancel. Order has no AWB number.');
            }

            $oldAWB = $order->awb_number;

            // Clear shipment data from order
            $order->update([
                'awb_number' => null,
                'shipping_status' => 'pending',
                'dispatched_date' => null,
                'delivered_date' => null,
            ]);
            
            // Delete tracking events
            \App\Models\ShippingTracking::where('order_id', $order->id)->delete();
            
            // ✅ LOG SHIPMENT CANCELLATION
            ActivityLogService::log(
                'edited',
                'orders',
                $order->id,
                'Order',
                "Cancelled shipment for order #{$order->order_id} - Old AWB: {$oldAWB}"
            );
            
            \Log::info('Shipment cancelled', [
                'order_id' => $order->id,
                'awb' => $oldAWB
            ]);
            
            return back()->with('success', 'Shipment cancelled successfully. You can re-dispatch this order.');
            
        } catch (\Exception $e) {
            \Log::error('Cancel shipment failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Sync single order from WooCommerce
     */
    public function syncSingle(Order $order)
    {
        if (!$order->woocommerce_order_id) {
            return back()->with('error', 'This order is not linked to WooCommerce.');
        }

        try {
            $wooService = app(\App\Services\WooCommerceService::class);
            $result = $wooService->syncSingleOrder($order->woocommerce_order_id);

            if ($result['success']) {
                // ✅ LOG SYNC
                ActivityLogService::log(
                    'edited',
                    'orders',
                    $order->id,
                    'Order',
                    "Synced order #{$order->order_id} from WooCommerce"
                );

                return back()->with('success', '✅ Order synced successfully from WooCommerce!');
            }

            return back()->with('error', '❌ Sync failed: ' . $result['message']);

        } catch (\Exception $e) {
            return back()->with('error', '❌ Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Correct orders.delivered_date using the real Delhivery scan timestamp.
     * Only processes orders that have an AWB number - orders marked delivered
     * without an AWB are skipped since there is no tracking data to correct
     * them from. Admin-only maintenance tool.
     */
    public function fixDeliveredDates(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only admins can run this tool.');
        }

        $orderNumber = trim($request->input('order_number', ''));

        $query = Order::where('shipping_status', 'delivered')
            ->whereNotNull('awb_number')
            ->where('awb_number', '!=', '');

        if ($orderNumber !== '') {
            $cleanNumber = ltrim($orderNumber, '#');
            $query->where(function ($q) use ($cleanNumber) {
                $q->where('order_id', $cleanNumber)
                  ->orWhere('woocommerce_order_id', $cleanNumber)
                  ->orWhere('id', $cleanNumber);
            });
        }

        $orders = $query->get();

        if ($orderNumber !== '' && $orders->isEmpty()) {
            return back()->with('error', "No delivered order with an AWB number found matching \"{$orderNumber}\".");
        }

        $corrected = [];
        $alreadyCorrect = 0;
        $skippedNoTracking = 0;

        foreach ($orders as $order) {
            $deliveredScan = \App\Models\ShippingTracking::where('order_id', $order->id)
                ->where('status', 'like', '%delivered%')
                ->orderBy('tracked_at', 'asc')
                ->first();

            if (!$deliveredScan) {
                $skippedNoTracking++;
                continue;
            }

            $correctDate = \Carbon\Carbon::parse($deliveredScan->tracked_at);
            $currentDate = $order->delivered_date;

            if ($currentDate && $currentDate->format('Y-m-d H:i') === $correctDate->format('Y-m-d H:i')) {
                $alreadyCorrect++;
                continue;
            }

            $corrected[] = [
                'order_number' => $order->order_id ?? $order->woocommerce_order_id,
                'old_delivered_date' => $currentDate ? $currentDate->format('d M Y, h:i A') : null,
                'new_delivered_date' => $correctDate->format('d M Y, h:i A'),
            ];

            $order->update(['delivered_date' => $correctDate]);
        }

        // Audit trail - so there's a record of who ran this maintenance tool and when
        ActivityLogService::log(
            'edited',
            'orders',
            0,
            'System',
            'Ran "Fix Delivered Dates" tool' . ($orderNumber !== '' ? " (order: {$orderNumber})" : ' (all orders)') .
            ': ' . count($corrected) . ' corrected, ' .
            $alreadyCorrect . ' already correct, ' . $skippedNoTracking . ' skipped (no tracking data)'
        );

        if (count($corrected) === 1) {
            $only = $corrected[0];
            return back()->with('success',
                "Order #{$only['order_number']} corrected: {$only['old_delivered_date']} → {$only['new_delivered_date']}"
            );
        }

        return back()->with('success',
            count($corrected) . ' order(s) corrected. ' .
            $alreadyCorrect . ' were already correct. ' .
            $skippedNoTracking . ' skipped (no AWB tracking history on file).'
        );
    }

    /**
     * Backfill orders.dye_status/print_status/emb_status/master_status from
     * each order's actual per-product statuses. Admin-only maintenance tool.
     */
    public function syncAllWorkflowStatuses(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only admins can run this tool.');
        }

        $orderNumber = trim($request->input('order_number', ''));

        $query = Order::has('products');

        if ($orderNumber !== '') {
            $cleanNumber = ltrim($orderNumber, '#');
            $query->where(function ($q) use ($cleanNumber) {
                $q->where('order_id', $cleanNumber)
                  ->orWhere('woocommerce_order_id', $cleanNumber)
                  ->orWhere('id', $cleanNumber);
            });
        }

        $orders = $query->get();

        if ($orderNumber !== '' && $orders->isEmpty()) {
            return back()->with('error', "No order with products found matching \"{$orderNumber}\".");
        }

        $updated = 0;

        foreach ($orders as $order) {
            $before = [
                $order->dye_status, $order->print_status,
                $order->emb_status, $order->master_status,
            ];

            $order->syncStageStatusesFromProducts();
            $order->refresh();

            $after = [
                $order->dye_status, $order->print_status,
                $order->emb_status, $order->master_status,
            ];

            if ($before !== $after) {
                $updated++;
            }
        }

        ActivityLogService::log(
            'edited',
            'orders',
            0,
            'System',
            'Ran "Sync Workflow Statuses" tool' . ($orderNumber !== '' ? " (order: {$orderNumber})" : ' (all orders)') .
            ": {$updated} order(s) updated out of {$orders->count()} checked"
        );

        return back()->with('success',
            "{$updated} order(s) had their workflow status corrected out of {$orders->count()} checked."
        );
    }
}

// namespace App\Http\Controllers;

// use App\Models\Order;
// use App\Models\OrderProduct;
// use App\Models\Customer;
// use App\Models\Vendor;
// use App\Models\ShippingPartner;
// use App\Models\OrderStatusHistory;
// use App\Services\ActivityLogService;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Storage;
// use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Log;
// use App\Services\DelhiveryService;
// use App\Services\RazorpayService;

// class OrderController extends Controller
// {
//     /**
//      * Display orders list
//      */
//     public function index(Request $request)
//     {
//         $query = Order::with(['customer', 'dyeVendor', 'printVendor', 'embVendor', 'masterVendor', 'shippingPartner', 'products']);
//         // Search
//         if ($request->filled('search')) {
//             $search = $request->search;
//             // Remove # if user typed it
//             $searchTerm = str_replace('#', '', $search);
            
//             $query->where(function($q) use ($search, $searchTerm) {
//                 $q->where('order_id', 'like', "%{$searchTerm}%")
//                   ->orWhere('order_id', 'like', "%#{$searchTerm}%")
//                   ->orWhere('woocommerce_order_id', 'like', "%{$search}%")           
//                   ->orWhere('awb_number', 'like', "%{$search}%")
//                   ->orWhere('product_description', 'like', "%{$search}%")
//                   ->orWhereHas('customer', function($q) use ($search) {
//                       $q->where('name', 'like', "%{$search}%")
//                         ->orWhere('phone', 'like', "%{$search}%")
//                         ->orWhere('email', 'like', "%{$search}%");
//                   });
//             });
//         }

//         // Order Status Filter (handle both 'status' and 'order_status')
//         if ($request->filled('status')) {
//             $query->where('order_status', $request->status);
//         }
        
//         if ($request->filled('order_status')) {
//             $query->where('order_status', $request->order_status);
//         }

//         // Payment Status Filter
//         if ($request->filled('payment_status')) {
//             $query->where('payment_status', $request->payment_status);
//         }

//         // Shipping Status Filter
//         if ($request->filled('shipping_status')) {
//             $query->where('shipping_status', $request->shipping_status);
//         }

//         // Source Filter (WooCommerce vs Manual)
//         if ($request->filled('source')) {
//             if ($request->source == 'woocommerce') {
//                 $query->whereNotNull('woocommerce_order_id');
//             } elseif ($request->source == 'manual') {
//                 $query->whereNull('woocommerce_order_id');
//             }
//         }

//         // Workflow Stage Filter
//         if ($request->filled('stage')) {
//             switch ($request->stage) {
//                 case 'dye':
//                     $query->where('dye_status', 'pending');
//                     break;
//                 case 'print':
//                     $query->where('print_status', 'pending');
//                     break;
//                 case 'emb':
//                     $query->where('emb_status', 'pending');
//                     break;
//                 case 'master':
//                     $query->where('master_status', 'pending');
//                     break;
//                 case 'shipping':
//                     $query->where('master_status', 'completed')
//                           ->whereNull('awb_number');
//                     break;
//             }
//         }

//         // Date Range Filters
//         if ($request->filled('date_from')) {
//             $query->whereDate('order_date', '>=', $request->date_from);
//         }

//         if ($request->filled('date_to')) {
//             $query->whereDate('order_date', '<=', $request->date_to);
//         }

//         // IMPORTANT: Add withQueryString() to preserve search/filters in pagination
//         $orders = $query->orderBy('woocommerce_order_id', 'desc')->paginate(20)->withQueryString();
        
//         // For filters dropdown
//         $vendors = Vendor::where('is_active', true)->get()->groupBy('type');
//         $shippingPartners = ShippingPartner::where('is_active', true)->get();

//         return view('orders.index', compact('orders', 'vendors', 'shippingPartners'));
//     }

//     /**
//      * Show create order form
//      */
//     public function create()
//     {
//         $customers = Customer::orderBy('name')->get();
//         $vendors = Vendor::where('is_active', true)->get()->groupBy('type');
//         $shippingPartners = ShippingPartner::where('is_active', true)->get();

//         return view('orders.create', compact('customers', 'vendors', 'shippingPartners'));
//     }

//     /**
//      * Check Razorpay payment for all pending orders
//      */
//     public function checkAllPayments()
//     {
//         $razorpayService = app(RazorpayService::class);

//         if (!$razorpayService->isConfigured()) {
//             return back()->with('error', 'Razorpay not configured.');
//         }

//         $orders = Order::whereIn('payment_status', ['pending', 'partial'])
//             ->whereNotNull('woocommerce_order_id')
//             ->limit(50)
//             ->get();

//         $checked = 0;
//         $updated = 0;

//         foreach ($orders as $order) {
//             $result = $razorpayService->checkPaymentStatus($order);
//             $checked++;
//             if ($result['success']) {
//                 $updated++;
//             }
            
//             // Sleep to avoid rate limiting
//             usleep(500000); // 0.5 seconds
//         }

//         return back()->with('success', "✅ Checked {$checked} orders, updated {$updated} payment statuses!");
//     }

//     /**
//      * Check Razorpay payment status for order
//      */
//     public function checkPayment(Order $order)
//     {
//         $razorpayService = app(RazorpayService::class);

//         if (!$razorpayService->isConfigured()) {
//             if (request()->ajax()) {
//                 return response()->json([
//                     'success' => false,
//                     'message' => 'Razorpay not configured'
//                 ]);
//             }
//             return back()->with('error', 'Razorpay not configured. Add credentials to .env file.');
//         }

//         // If payment notes has Razorpay ID, fetch directly
//         if ($order->payment_notes && str_contains($order->payment_notes, 'pay_')) {
//             preg_match('/pay_[a-zA-Z0-9]+/', $order->payment_notes, $matches);
//             if (!empty($matches[0])) {
//                 $result = $razorpayService->fetchPayment($matches[0]);
//                 if ($result['success']) {
//                     // Update order
//                     $paymentStatus = $result['status'] === 'captured' ? 'received' : 'pending';
//                     $order->update([
//                         'payment_status' => $paymentStatus,
//                         'paid_amount' => $result['status'] === 'captured' ? $result['amount'] : $order->paid_amount,
//                         'razorpay_payment_status' => $result['status'],
//                         'razorpay_amount' => $result['amount'],
//                         'razorpay_payment_method' => $result['method'],
//                         'razorpay_checked_at' => now(),
//                     ]);
                    
//                     if (request()->ajax()) {
//                         return response()->json([
//                             'success' => true,
//                             'message' => "✅ Payment {$result['status']}!",
//                             'razorpay_status' => $result['status'],
//                             'payment_status' => $paymentStatus,
//                             'amount' => $result['amount'],
//                             'payment_method' => $result['method'],
//                         ]);
//                     }
                    
//                     return back()->with('success', "✅ Payment {$result['status']}! Amount: ₹{$result['amount']}");
//                 }
//             }
//         }

//         // Otherwise search by order
//         $result = $razorpayService->checkPaymentStatus($order);

//         if ($result['success']) {
//             if (request()->ajax()) {
//                 return response()->json([
//                     'success' => true,
//                     'message' => "✅ Payment {$result['status']}!",
//                     'razorpay_status' => $result['status'],
//                     'payment_status' => $result['crm_status'],
//                     'amount' => $result['amount'],
//                     'payment_method' => $result['method'],
//                 ]);
//             }
            
//             return back()->with('success', "✅ Payment {$result['status']}! Razorpay ID: {$result['payment_id']}");
//         }

//         if (request()->ajax()) {
//             return response()->json([
//                 'success' => false,
//                 'message' => $result['message']
//             ]);
//         }

//         return back()->with('error', '❌ ' . $result['message']);
//     }

//     /**
//      * Store new order
//      */
//     // public function store(Request $request)
//     // {
//     //     $validated = $request->validate([
//     //         'customer_id' => 'required|exists:customers,id',
//     //         'order_date' => 'required|date',
//     //         'amount' => 'required|numeric|min:0',
//     //         'product_image' => 'nullable|image|max:5120',
//     //         'product_description' => 'nullable|string',
            
//     //         'dye_vendor_id' => 'nullable|exists:vendors,id',
//     //         'dye_status' => 'required|in:pending,received,completed,na',
//     //         'dye_received_date' => 'nullable|date',
            
//     //         'print_vendor_id' => 'nullable|exists:vendors,id',
//     //         'print_status' => 'required|in:pending,received,completed,na',
//     //         'print_received_date' => 'nullable|date',
            
//     //         'emb_vendor_id' => 'nullable|exists:vendors,id',
//     //         'emb_status' => 'required|in:pending,received,completed,na',
//     //         'emb_received_date' => 'nullable|date',
            
//     //         'master_vendor_id' => 'nullable|exists:vendors,id',
//     //         'master_status' => 'required|in:pending,received,completed',
//     //         'master_received_date' => 'nullable|date',
            
//     //         'payment_status' => 'required|in:pending,partial,received,remittance_balance',
//     //         'paid_amount' => 'nullable|numeric|min:0',
//     //         'payment_notes' => 'nullable|string',
//     //     ]);

//     //     // Generate order ID
//     //     $lastOrder = Order::latest('id')->first();
//     //     $orderNumber = $lastOrder ? (int) str_replace('#', '', $lastOrder->order_id) + 1 : 4134;
//     //     $validated['order_id'] = '#' . $orderNumber;

//     //     // Handle product image upload
//     //     if ($request->hasFile('product_image')) {
//     //         $validated['product_image'] = $request->file('product_image')->store('products', 'public');
//     //     }

//     //     // Determine overall order status
//     //     $validated['order_status'] = $this->determineOrderStatus($validated);

//     //     $order = Order::create($validated);

//     //     // Create status history
//     //     OrderStatusHistory::create([
//     //         'order_id' => $order->id,
//     //         'stage' => 'order',
//     //         'new_status' => 'created',
//     //         'notes' => 'Order created',
//     //         'updated_by' => auth()->id(),
//     //     ]);
        
//     //     // ✅ LOG ORDER CREATION
//     //     ActivityLogService::log(
//     //         'created',
//     //         'orders',
//     //         $order->id,
//     //         'Order',
//     //         "Created order: #{$order->order_id} from {$order->customer->name}"
//     //     );

//     //     return redirect()->route('orders.show', $order)
//     //         ->with('success', 'Order created successfully!');
//     // }
// /**
//  * Store new order
//  */
// public function store(Request $request)
// {
//     $validated = $request->validate([
//         'customer_id' => 'required|exists:customers,id',
//         'order_date' => 'required|date',
//         'amount' => 'required|numeric|min:0',
//         'product_image' => 'nullable|image|max:5120',
//         'product_description' => 'nullable|string',
        
//         'dye_vendor_id' => 'nullable|exists:vendors,id',
//         'dye_status' => 'required|in:pending,received,completed,na',
//         'dye_received_date' => 'nullable|date',
        
//         'print_vendor_id' => 'nullable|exists:vendors,id',
//         'print_status' => 'required|in:pending,received,completed,na',
//         'print_received_date' => 'nullable|date',
        
//         'emb_vendor_id' => 'nullable|exists:vendors,id',
//         'emb_status' => 'required|in:pending,received,completed,na',
//         'emb_received_date' => 'nullable|date',
        
//         'master_vendor_id' => 'nullable|exists:vendors,id',
//         'master_status' => 'required|in:pending,received,completed',
//         'master_received_date' => 'nullable|date',
        
//         'payment_status' => 'required|in:pending,partial,received,remittance_balance',
//         'paid_amount' => 'nullable|numeric|min:0',
//         'payment_notes' => 'nullable|string',
//     ]);

//     DB::beginTransaction();
//     try {
//         // ✅ LOCK FOR UPDATE - Prevent concurrent ID duplication
//         $lastOrder = Order::lockForUpdate()->latest('id')->first();
//         $orderNumber = $lastOrder ? (int) str_replace('#', '', $lastOrder->order_id) + 1 : 4134;
//         $validated['order_id'] = '#' . $orderNumber;

//         // Handle product image upload
//         if ($request->hasFile('product_image')) {
//             $validated['product_image'] = $request->file('product_image')->store('products', 'public');
//         }

//         // Determine overall order status
//         $validated['order_status'] = $this->determineOrderStatus($validated);

//         // Create order
//         $order = Order::create($validated);

//         // Create status history
//         OrderStatusHistory::create([
//             'order_id' => $order->id,
//             'stage' => 'order',
//             'new_status' => 'created',
//             'notes' => 'Order created',
//             'updated_by' => auth()->id(),
//         ]);
        
//         // ✅ LOG ORDER CREATION
//         ActivityLogService::log(
//             'created',
//             'orders',
//             $order->id,
//             'Order',
//             "Created order: #{$order->order_id} from {$order->customer->name}"
//         );

//         DB::commit();

//         return redirect()->route('orders.show', $order)
//             ->with('success', 'Order created successfully!');

//     } catch (\Exception $e) {
//         DB::rollBack();
        
//         // Log the error
//         Log::error('Order creation failed', [
//             'error' => $e->getMessage(),
//             'trace' => $e->getTraceAsString()
//         ]);

//         // Activity log for failure
//         ActivityLogService::log(
//             'error',
//             'orders',
//             'failed_create_' . now()->timestamp,
//             'Order',
//             "Failed to create order: " . $e->getMessage()
//         );

//         return back()
//             ->withInput()
//             ->with('error', 'Error creating order: ' . $e->getMessage());
//     }
// }
//     /**
//      * Display order details
//      */
//     public function show(Order $order)
//     {
       
//         $order->load([
//     'customer',
//     'dyeVendor',
//     'printVendor',
//     'embVendor',
//     'masterVendor',
//     'shippingPartner',
//     'statusHistory.updatedBy',
//     'shippingTracking',
//     'products.dyeVendor',   // ← add these
//     'products.printVendor',
//     'products.embVendor',
//     'products.masterVendor',
// ]);

//         $vendors = Vendor::where('is_active', true)->get()->groupBy('type');
//         $shippingPartners = ShippingPartner::where('is_active', true)->get();

//         return view('orders.show', compact('order', 'vendors', 'shippingPartners'));
//     }

//     /**
//      * Show edit form
//      */
//     // public function edit(Order $order)
//     // {
//     //     // Load products with their vendors
//     //     $order->load([
//     //         'products.dyeVendor',
//     //         'products.printVendor',
//     //         'products.embVendor',
//     //         'products.masterVendor'
//     //     ]);
        
//     //     $customers = Customer::orderBy('name')->get();
//     //     $vendors = Vendor::where('is_active', true)->get()->groupBy('type');
//     //     $shippingPartners = ShippingPartner::where('is_active', true)->get();
        
//     //     return view('orders.edit', compact('order', 'customers', 'vendors', 'shippingPartners'));
//     // }
    
//     public function edit(Request $request, Order $order)
// {
//     // Load products with their vendors
//     $order->load([
//         'products.dyeVendor',
//         'products.printVendor',
//         'products.embVendor',
//         'products.masterVendor'
//     ]);
    
//     $customers = Customer::orderBy('name')->get();
//     $vendors = Vendor::where('is_active', true)->get()->groupBy('type');
//     $shippingPartners = ShippingPartner::where('is_active', true)->get();

//     // Preserve the page/filters the user came from so "Back to Orders" can return them there
//     $backParams = $request->only([
//         'page', 'search', 'status', 'order_status', 'payment_status',
//         'shipping_status', 'source', 'stage', 'date_from', 'date_to',
//     ]);
    
//     return view('orders.edit', compact('order', 'customers', 'vendors', 'shippingPartners', 'backParams'));
// }

//     /**
//      * Update order
//      */
//     public function update(Request $request, Order $order)
//     {
//         // ✅ CAPTURE OLD DATA BEFORE UPDATE
//         $oldData = $order->toArray();

//         $validated = $request->validate([
//             'customer_id' => 'required|exists:customers,id',
//             'order_date' => 'required|date',
//             'amount' => 'required|numeric|min:0',
//             'paid_amount' => 'nullable|numeric|min:0',
//             'payment_status' => 'required|in:pending,received,refunded',
//              'payment_notes' => 'nullable|string',
//             'payment_gateway' => 'required|in:razorpay,cod,bank_transfer,cheque,other',
//             'order_status' => 'required|in:new,processing,dispatched,delivered,cancelled,refunded,failed,rto_pay_pending,exchange_received,exchange_completed,refund_requested,refund_approved,refund_cancelled',
//             'product_description' => 'nullable|string',
//             'remark' => 'nullable|string',
//             'shipping_partner_id' => 'nullable|exists:shipping_partners,id',
//             'awb_number' => 'nullable|string',
//             'shipping_status' => 'nullable|in:pending,dispatched,in_transit,out_for_delivery,delivered',
            
//             // Product workflow validation
//             'products' => 'sometimes|array',
//             'products.*.id' => 'sometimes|required|exists:order_products,id',
//             'products.*.dye_status' => 'sometimes|required|in:pending,in_progress,completed',
//             'products.*.dye_vendor_id' => 'nullable|exists:vendors,id',
//             'products.*.dye_received_date' => 'nullable|date',
//             'products.*.print_status' => 'sometimes|required|in:pending,in_progress,completed',
//             'products.*.print_vendor_id' => 'nullable|exists:vendors,id',
//             'products.*.print_received_date' => 'nullable|date',
//             'products.*.emb_status' => 'sometimes|required|in:pending,in_progress,completed',
//             'products.*.emb_vendor_id' => 'nullable|exists:vendors,id',
//             'products.*.emb_received_date' => 'nullable|date',
//             'products.*.master_status' => 'sometimes|required|in:pending,in_progress,completed',
//             'products.*.master_vendor_id' => 'nullable|exists:vendors,id',
//             'products.*.master_received_date' => 'nullable|date',
//         ]);

//         // Update order basic info
//         $order->update([
//             'customer_id' => $validated['customer_id'],
//             'order_date' => $validated['order_date'],
//             'amount' => $validated['amount'],
//             'paid_amount' => $validated['paid_amount'] ?? 0,
//             'payment_status' => $validated['payment_status'],
//              'payment_notes' => $validated['payment_notes'] ?? null,
//             'payment_gateway' => $validated['payment_gateway'],
//             'order_status' => $validated['order_status'],
//             'product_description' => $validated['product_description'] ?? null,
//             'remark' => $validated['remark'] ?? null,
//             'shipping_partner_id' => $validated['shipping_partner_id'] ?? null,
//             'awb_number' => $validated['awb_number'] ?? null,
//             'shipping_status' => $validated['shipping_status'] ?? 'pending',
//         ]);

//         // Update each product's workflow
//         if (isset($validated['products']) && is_array($validated['products'])) {
//             foreach ($validated['products'] as $productData) {
//                 OrderProduct::where('id', $productData['id'])
//                     ->where('order_id', $order->id) // Security check
//                     ->update([
//                         'dye_status' => $productData['dye_status'],
//                         'dye_vendor_id' => $productData['dye_vendor_id'] ?? null,
//                         'dye_received_date' => $productData['dye_received_date'] ?? null,
//                         'print_status' => $productData['print_status'],
//                         'print_vendor_id' => $productData['print_vendor_id'] ?? null,
//                         'print_received_date' => $productData['print_received_date'] ?? null,
//                         'emb_status' => $productData['emb_status'],
//                         'emb_vendor_id' => $productData['emb_vendor_id'] ?? null,
//                         'emb_received_date' => $productData['emb_received_date'] ?? null,
//                         'master_status' => $productData['master_status'],
//                         'master_vendor_id' => $productData['master_vendor_id'] ?? null,
//                         'master_received_date' => $productData['master_received_date'] ?? null,
//                     ]);
//             }
//         }

//         // ✅ LOG ORDER UPDATE
//         ActivityLogService::log(
//             'edited',
//             'orders',
//             $order->id,
//             'Order',
//             "Updated order: #{$order->order_id}",
//             $oldData,
//             $order->toArray()
//         );

//         return redirect()->route('orders.show', $order)
//             ->with('success', 'Order updated successfully!');
//     }

//     /**
//      * Delete order
//      */
//     public function destroy(Order $order)
//     {
//         // ✅ CAPTURE ORDER DATA BEFORE DELETION
//         $orderNumber = $order->order_id;
//         $customerName = $order->customer->name ?? 'Unknown';

//         // Delete product image
//         if ($order->product_image) {
//             Storage::disk('public')->delete($order->product_image);
//         }

//         // ✅ LOG ORDER DELETION
//         ActivityLogService::log(
//             'deleted',
//             'orders',
//             $order->id,
//             'Order',
//             "Deleted order: #{$orderNumber} from {$customerName}"
//         );

//         $order->delete();

//         return redirect()->route('orders.index')
//             ->with('success', 'Order deleted successfully!');
//     }

//     /**
//      * Quick update stage status
//      */
//     public function updateStage(Request $request, Order $order)
//     {
//         $validated = $request->validate([
//             'stage' => 'required|in:dye,print,emb,master,shipping',
//             'status' => 'required|string',
//             'vendor_id' => 'nullable|exists:vendors,id',
//             'received_date' => 'nullable|date',
//             'notes' => 'nullable|string',
//         ]);

//         $stage = $validated['stage'];
//         $oldStatus = $order->{$stage . '_status'};

//         // Update stage status
//         $order->{$stage . '_status'} = $validated['status'];
        
//         if (isset($validated['vendor_id'])) {
//             $order->{$stage . '_vendor_id'} = $validated['vendor_id'];
//         }
        
//         if (isset($validated['received_date'])) {
//             $order->{$stage . '_received_date'} = $validated['received_date'];
//         }

//         // Update overall order status
//         $order->order_status = $this->determineOrderStatus($order->toArray());
        
//         $order->save();

//         // Log status change
//         OrderStatusHistory::create([
//             'order_id' => $order->id,
//             'stage' => $stage,
//             'old_status' => $oldStatus,
//             'new_status' => $validated['status'],
//             'notes' => $validated['notes'] ?? null,
//             'updated_by' => auth()->id(),
//         ]);

//         // ✅ LOG STAGE UPDATE
//         ActivityLogService::log(
//             'edited',
//             'orders',
//             $order->id,
//             'Order',
//             "Changed {$stage} stage from {$oldStatus} to {$validated['status']}"
//         );

//         return response()->json([
//             'success' => true,
//             'message' => ucfirst($stage) . ' stage updated successfully!',
//             'order_status' => $order->order_status,
//         ]);
//     }

//     /**
//      * Update shipping details
//      */
//     public function updateShipping(Request $request, Order $order)
//     {
//         $validated = $request->validate([
//             'shipping_partner_id' => 'required|exists:shipping_partners,id',
//             'awb_number' => 'required|string',
//             'dispatched_date' => 'required|date',
//             'notes' => 'nullable|string',
//         ]);

//         $oldStatus = $order->shipping_status;
//         $oldAWB = $order->awb_number;

//         $order->update([
//             'shipping_partner_id' => $validated['shipping_partner_id'],
//             'awb_number' => $validated['awb_number'],
//             'dispatched_date' => $validated['dispatched_date'],
//             'shipping_status' => 'dispatched',
//             'order_status' => 'dispatched',
//         ]);

//         // Log status change
//         OrderStatusHistory::create([
//             'order_id' => $order->id,
//             'stage' => 'shipping',
//             'old_status' => $oldStatus,
//             'new_status' => 'dispatched',
//             'notes' => $validated['notes'] ?? 'Order dispatched with AWB: ' . $validated['awb_number'],
//             'updated_by' => auth()->id(),
//         ]);

//         // ✅ LOG SHIPPING UPDATE
//         ActivityLogService::log(
//             'edited',
//             'orders',
//             $order->id,
//             'Order',
//             "Updated shipping: AWB {$oldAWB} → {$validated['awb_number']}"
//         );

//         return response()->json([
//             'success' => true,
//             'message' => 'Shipping details updated successfully!',
//         ]);
//     }

//     /**
//      * Update payment status
//      */
//     public function updatePayment(Request $request, Order $order)
//     {
//         $validated = $request->validate([
//             'payment_status' => 'required|in:pending,partial,received,remittance_balance',
//             'paid_amount' => 'nullable|numeric|min:0',
//             'payment_notes' => 'nullable|string',
//         ]);

//         $oldStatus = $order->payment_status;
//         $oldAmount = $order->paid_amount;

//         $order->update($validated);

//         // Log payment update
//         OrderStatusHistory::create([
//             'order_id' => $order->id,
//             'stage' => 'payment',
//             'old_status' => $order->payment_status,
//             'new_status' => $validated['payment_status'],
//             'notes' => $validated['payment_notes'] ?? 'Payment status updated',
//             'updated_by' => auth()->id(),
//         ]);

//         // ✅ LOG PAYMENT UPDATE
//         ActivityLogService::log(
//             'edited',
//             'orders',
//             $order->id,
//             'Order',
//             "Updated payment: {$oldStatus} (₹{$oldAmount}) → {$validated['payment_status']} (₹{$validated['paid_amount']})"
//         );

//         return response()->json([
//             'success' => true,
//             'message' => 'Payment status updated successfully!',
//         ]);
//     }

//     /**
//      * Bulk update orders
//      */
//     public function bulkUpdate(Request $request)
//     {
//         $validated = $request->validate([
//             'order_ids' => 'required|array',
//             'order_ids.*' => 'exists:orders,id',
//             'action' => 'required|in:update_status,assign_vendor,update_payment',
//             'data' => 'required|array',
//         ]);

//         $orders = Order::whereIn('id', $validated['order_ids'])->get();
//         $updated = 0;

//         foreach ($orders as $order) {
//             try {
//                 switch ($validated['action']) {
//                     case 'update_status':
//                         if (isset($validated['data']['stage']) && isset($validated['data']['status'])) {
//                             $stage = $validated['data']['stage'];
//                             $order->{$stage . '_status'} = $validated['data']['status'];
//                             $order->save();
//                             $updated++;

//                             // ✅ LOG BULK STATUS UPDATE
//                             ActivityLogService::log(
//                                 'edited',
//                                 'orders',
//                                 $order->id,
//                                 'Order',
//                                 "Bulk updated: {$stage} status to {$validated['data']['status']}"
//                             );
//                         }
//                         break;

//                     case 'assign_vendor':
//                         if (isset($validated['data']['stage']) && isset($validated['data']['vendor_id'])) {
//                             $stage = $validated['data']['stage'];
//                             $order->{$stage . '_vendor_id'} = $validated['data']['vendor_id'];
//                             $order->save();
//                             $updated++;

//                             // ✅ LOG BULK VENDOR ASSIGNMENT
//                             ActivityLogService::log(
//                                 'edited',
//                                 'orders',
//                                 $order->id,
//                                 'Order',
//                                 "Bulk assigned vendor for {$stage} stage"
//                             );
//                         }
//                         break;

//                     case 'update_payment':
//                         if (isset($validated['data']['payment_status'])) {
//                             $order->payment_status = $validated['data']['payment_status'];
//                             $order->save();
//                             $updated++;

//                             // ✅ LOG BULK PAYMENT UPDATE
//                             ActivityLogService::log(
//                                 'edited',
//                                 'orders',
//                                 $order->id,
//                                 'Order',
//                                 "Bulk updated payment status to {$validated['data']['payment_status']}"
//                             );
//                         }
//                         break;
//                 }
//             } catch (\Exception $e) {
//                 Log::error('Bulk update failed for order ' . $order->id . ': ' . $e->getMessage());
//             }
//         }

//         return response()->json([
//             'success' => true,
//             'message' => "{$updated} orders updated successfully!",
//         ]);
//     }

//     /**
//      * Export orders to Excel
//      */
//     public function export(Request $request)
//     {
//         $query = Order::with(['customer', 'dyeVendor', 'printVendor', 'embVendor', 'masterVendor', 'shippingPartner']);

//         // Apply same filters as index
//         if ($request->filled('status')) {
//             $query->where('order_status', $request->status);
//         }

//         $orders = $query->get();

//         $filename = 'orders_' . now()->format('Y-m-d_His') . '.csv';
//         $headers = [
//             'Content-Type' => 'text/csv',
//             'Content-Disposition' => "attachment; filename=\"{$filename}\"",
//         ];

//         $callback = function() use ($orders) {
//             $file = fopen('php://output', 'w');
            
//             // Headers
//             fputcsv($file, [
//                 'SR.NO', 'Date', 'Order ID', 'Customer Name', 'Product Image', 
//                 'Dye', 'Print', 'Emb', 'Master', 'Status',
//                 'Shipping Partner', 'Dispatched Date', 'AWB No', 'Order Status',
//                 'Amount', 'Payment Status'
//             ]);

//             // Data
//             foreach ($orders as $index => $order) {
//                 fputcsv($file, [
//                     $index + 1,
//                     $order->order_date->format('d M Y'),
//                     $order->order_id,
//                     $order->customer->name,
//                     $order->product_image ? asset('storage/' . $order->product_image) : '',
//                     $order->dyeVendor ? $order->dyeVendor->name : 'NA',
//                     $order->printVendor ? $order->printVendor->name : 'NA',
//                     $order->embVendor ? $order->embVendor->name : 'NA',
//                     $order->masterVendor ? $order->masterVendor->name : '',
//                     strtoupper($order->order_status),
//                     $order->shippingPartner ? $order->shippingPartner->name : '',
//                     $order->dispatched_date ? $order->dispatched_date->format('d M') : '',
//                     $order->awb_number ?? '',
//                     strtoupper(str_replace('_', ' ', $order->shipping_status)),
//                     $order->amount,
//                     strtoupper(str_replace('_', ' ', $order->payment_status)),
//                 ]);
//             }

//             fclose($file);
//         };

//         return response()->stream($callback, 200, $headers);
//     }

//     /**
//      * Determine overall order status based on stages
//      */
//     protected function determineOrderStatus($data)
//     {
//         // If delivered
//         if (isset($data['shipping_status']) && $data['shipping_status'] === 'delivered') {
//             return 'delivered';
//         }

//         // If dispatched
//         if (isset($data['shipping_status']) && in_array($data['shipping_status'], ['dispatched', 'in_transit', 'out_for_delivery'])) {
//             return 'dispatched';
//         }

//         // If any stage is in progress
//         $stages = ['dye_status', 'print_status', 'emb_status', 'master_status'];
//         foreach ($stages as $stage) {
//             if (isset($data[$stage]) && in_array($data[$stage], ['received', 'completed'])) {
//                 return 'processing';
//             }
//         }

//         return 'new';
//     }

//     /**
//      * Log status changes for audit trail
//      */
//     protected function logStatusChanges($order, $newData)
//     {
//         $stages = [
//             'dye' => 'dye_status',
//             'print' => 'print_status',
//             'emb' => 'emb_status',
//             'master' => 'master_status',
//             'shipping' => 'shipping_status',
//         ];

//         foreach ($stages as $stageName => $statusField) {
//             if (isset($newData[$statusField]) && $order->$statusField !== $newData[$statusField]) {
//                 OrderStatusHistory::create([
//                     'order_id' => $order->id,
//                     'stage' => $stageName,
//                     'old_status' => $order->$statusField,
//                     'new_status' => $newData[$statusField],
//                     'notes' => 'Status updated',
//                     'updated_by' => auth()->id(),
//                 ]);
//             }
//         }

//         // Log payment status change
//         if (isset($newData['payment_status']) && $order->payment_status !== $newData['payment_status']) {
//             OrderStatusHistory::create([
//                 'order_id' => $order->id,
//                 'stage' => 'payment',
//                 'old_status' => $order->payment_status,
//                 'new_status' => $newData['payment_status'],
//                 'notes' => 'Payment status updated',
//                 'updated_by' => auth()->id(),
//             ]);
//         }
//     }

//     /**
//      * Refresh tracking information from Delhivery
//      */
//  public function refreshTracking(Order $order)
// {
//     try {
//         if (!$order->awb_number) {
//             return back()->with('error', 'No AWB number found for this order.');
//         }

//         $delhiveryService = app(\App\Services\DelhiveryService::class);

//         if (!$delhiveryService->isConfigured()) {
//             return back()->with('error', 'Delhivery API not configured.');
//         }

//         $result = $delhiveryService->trackShipment($order->awb_number);

//         if ($result['success']) {
//             // Update order shipping status
//             $shippingStatus = $delhiveryService->mapStatusToCRM($result['status'] ?? 'pending');
//             $updateData = ['shipping_status' => $shippingStatus];

//             if ($shippingStatus === 'delivered') {
//                 // Find the actual delivery timestamp from Delhivery's scan history
//                 // instead of stamping the time the refresh button happened to be clicked.
//                 $deliveredAt = null;

//                 if (!empty($result['scans']) && is_array($result['scans'])) {
//                     foreach ($result['scans'] as $scan) {
//                         if (empty($scan['date_time']) || empty($scan['status'])) {
//                             continue;
//                         }
//                         if (stripos($scan['status'], 'delivered') === false) {
//                             continue;
//                         }
//                         try {
//                             $scanDate = \Carbon\Carbon::parse($scan['date_time']);
//                         } catch (\Exception $e) {
//                             continue;
//                         }
//                         if (!$deliveredAt || $scanDate->lt($deliveredAt)) {
//                             $deliveredAt = $scanDate;
//                         }
//                     }
//                 }

//                 if ($deliveredAt) {
//                     $updateData['delivered_date'] = $deliveredAt;
//                 } elseif (!$order->delivered_date) {
//                     // Fallback only if we have no delivery date on record at all
//                     $updateData['delivered_date'] = now();
//                 }
//             }
//             // Note: if not delivered, we deliberately leave delivered_date untouched
//             // so a stale/glitchy courier status can't wipe out a previously recorded date.

//             $order->update($updateData);

//             // Delete old tracking events to avoid duplicates
//             \App\Models\ShippingTracking::where('order_id', $order->id)->delete();

//             // Save tracking events
//             if (!empty($result['scans']) && is_array($result['scans'])) {
//                 foreach ($result['scans'] as $scan) {
//                     // Skip if date_time is empty
//                     if (empty($scan['date_time'])) {
//                         continue;
//                     }

//                     // Parse datetime
//                     try {
//                         $trackedAt = \Carbon\Carbon::parse($scan['date_time']);
//                     } catch (\Exception $e) {
//                         \Log::warning('Invalid datetime format', [
//                             'order_id' => $order->id,
//                             'date_time' => $scan['date_time']
//                         ]);
//                         continue;
//                     }

//                     // Use tracked_at column
//                     \App\Models\ShippingTracking::create([
//                         'order_id' => $order->id,
//                         'awb_number' => $order->awb_number,
//                         'tracked_at' => $trackedAt,
//                         'status' => $scan['status'] ?? 'Unknown',
//                         'location' => $scan['location'] ?? 'Unknown',
//                         'remarks' => $scan['remarks'] ?? null,
//                         'raw_data' => json_encode($scan),
//                     ]);
//                 }
//             }

//             // ✅ LOG TRACKING REFRESH
//             ActivityLogService::log(
//                 'edited',
//                 'orders',
//                 $order->id,
//                 'Order',
//                 "Refreshed tracking for order #{$order->order_id} - Status: {$shippingStatus}"
//             );

//             return back()->with('success', 'Tracking information updated successfully!');
//         }

//         return back()->with('error', 'Failed to fetch tracking: ' . ($result['message'] ?? 'Unknown error'));

//     } catch (\Exception $e) {
//         \Log::error('Refresh tracking failed', [
//             'order_id' => $order->id,
//             'error' => $e->getMessage()
//         ]);

//         return back()->with('error', 'Error: ' . $e->getMessage());
//     }
// }

//     /**
//      * Auto-dispatch order with Delhivery
//      */
//   public function autoDispatch(Order $order)
// {
//     try {
//         if (in_array($order->order_status, ['delivered', 'cancelled', 'refunded'])) {
//     return back()->with('error', 'Cannot dispatch an order with status: ' . $order->order_status);
// }
//         // Payment check - skip for COD
//         if ($order->payment_gateway !== 'cod' && $order->payment_status !== 'received') {
//             return back()->with('error', 'Payment not received yet.');
//         }

//         // Production check - based on products
//         $order->loadMissing('products');
//         if ($order->products->isEmpty() || !$order->products->every(fn($p) => $p->master_status == 'completed')) {
//             return back()->with('error', 'Production not completed yet.');
//         }

//         if ($order->awb_number) {
//             return back()->with('warning', 'Order already has an AWB number.');
//         }

//         // Call Delhivery to create shipment
//         $delhiveryService = app(\App\Services\DelhiveryService::class);
//         $result = $delhiveryService->createShipment($order);

//         if ($result['success']) {
//             // ✅ LOG AUTO-DISPATCH
//             ActivityLogService::log(
//                 'edited',
//                 'orders',
//                 $order->id,
//                 'Order',
//                 "Auto-dispatched order #{$order->order_id} - AWB: {$result['awb_number']}"
//             );
//             return back()->with('success', 'Order dispatched successfully! AWB: ' . $result['awb_number']);
//         }

//         return back()->with('error', 'Dispatch failed: ' . $result['message']);

//     } catch (\Exception $e) {
//         return back()->with('error', 'Error: ' . $e->getMessage());
//     }
// }

//     /**
//      * Cancel shipment
//      */
//     public function cancelShipment(Order $order)
//     {
//         try {
//             if (!$order->awb_number) {
//                 return back()->with('error', 'No shipment to cancel. Order has no AWB number.');
//             }

//             $oldAWB = $order->awb_number;

//             // Clear shipment data from order
//             $order->update([
//                 'awb_number' => null,
//                 'shipping_status' => 'pending',
//                 'dispatched_date' => null,
//                 'delivered_date' => null,
//             ]);
            
//             // Delete tracking events
//             \App\Models\ShippingTracking::where('order_id', $order->id)->delete();
            
//             // ✅ LOG SHIPMENT CANCELLATION
//             ActivityLogService::log(
//                 'edited',
//                 'orders',
//                 $order->id,
//                 'Order',
//                 "Cancelled shipment for order #{$order->order_id} - Old AWB: {$oldAWB}"
//             );
            
//             \Log::info('Shipment cancelled', [
//                 'order_id' => $order->id,
//                 'awb' => $oldAWB
//             ]);
            
//             return back()->with('success', 'Shipment cancelled successfully. You can re-dispatch this order.');
            
//         } catch (\Exception $e) {
//             \Log::error('Cancel shipment failed', [
//                 'order_id' => $order->id,
//                 'error' => $e->getMessage()
//             ]);
            
//             return back()->with('error', 'Error: ' . $e->getMessage());
//         }
//     }

//     /**
//      * Sync single order from WooCommerce
//      */
//     public function syncSingle(Order $order)
//     {
//         if (!$order->woocommerce_order_id) {
//             return back()->with('error', 'This order is not linked to WooCommerce.');
//         }

//         try {
//             $wooService = app(\App\Services\WooCommerceService::class);
//             $result = $wooService->syncSingleOrder($order->woocommerce_order_id);

//             if ($result['success']) {
//                 // ✅ LOG SYNC
//                 ActivityLogService::log(
//                     'edited',
//                     'orders',
//                     $order->id,
//                     'Order',
//                     "Synced order #{$order->order_id} from WooCommerce"
//                 );

//                 return back()->with('success', '✅ Order synced successfully from WooCommerce!');
//             }

//             return back()->with('error', '❌ Sync failed: ' . $result['message']);

//         } catch (\Exception $e) {
//             return back()->with('error', '❌ Error: ' . $e->getMessage());
//         }
//     }
    
//     /**
//  * Correct orders.delivered_date using the real Delhivery scan timestamp.
//  * Only processes orders that have an AWB number - orders marked delivered
//  * without an AWB are skipped since there is no tracking data to correct
//  * them from. Admin-only maintenance tool.
//  */
// public function fixDeliveredDates(Request $request)
// {
//     if (!auth()->user()->isAdmin()) {
//         abort(403, 'Only admins can run this tool.');
//     }

//     $orders = Order::where('shipping_status', 'delivered')
//         ->whereNotNull('awb_number')
//         ->where('awb_number', '!=', '')
//         ->get();

//     $corrected = [];
//     $alreadyCorrect = 0;
//     $skippedNoTracking = 0;

//     foreach ($orders as $order) {
//         $deliveredScan = \App\Models\ShippingTracking::where('order_id', $order->id)
//             ->where('status', 'like', '%delivered%')
//             ->orderBy('tracked_at', 'asc')
//             ->first();

//         if (!$deliveredScan) {
//             $skippedNoTracking++;
//             continue;
//         }

//         $correctDate = \Carbon\Carbon::parse($deliveredScan->tracked_at);
//         $currentDate = $order->delivered_date;

//         if ($currentDate && $currentDate->format('Y-m-d H:i') === $correctDate->format('Y-m-d H:i')) {
//             $alreadyCorrect++;
//             continue;
//         }

//         $corrected[] = [
//             'order_number' => $order->order_id ?? $order->woocommerce_order_id,
//             'old_delivered_date' => $currentDate ? $currentDate->format('d M Y, h:i A') : null,
//             'new_delivered_date' => $correctDate->format('d M Y, h:i A'),
//         ];

//         $order->update(['delivered_date' => $correctDate]);
//     }

//     // Audit trail - so there's a record of who ran this maintenance tool and when
//     ActivityLogService::log(
//         'edited',
//         'orders',
//         0,
//         'System',
//         'Ran "Fix Delivered Dates" tool: ' . count($corrected) . ' corrected, ' .
//         $alreadyCorrect . ' already correct, ' . $skippedNoTracking . ' skipped (no tracking data)'
//     );

//     return back()->with('success',
//         count($corrected) . ' order(s) corrected. ' .
//         $alreadyCorrect . ' were already correct. ' .
//         $skippedNoTracking . ' skipped (no AWB tracking history on file).'
//     );
// }
// }
