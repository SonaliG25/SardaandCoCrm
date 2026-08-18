<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VendorDashboardController extends Controller
{
    /**
     * Show vendor dashboard
     */
    public function index()
    {
        $vendor = Auth::user()->vendor;
        
        if (!$vendor) {
            abort(403, 'No vendor profile associated with this account');
        }
        
        // Get orders for this vendor based on their type
        $orders = $this->getVendorOrders($vendor);
        
        // Calculate stats
        $stats = $this->calculateStats($vendor, $orders);
        
        return view('vendors.dashboard', compact('vendor', 'orders', 'stats'));
    }
    
    /**
     * Show my orders page
     */
    public function myOrders(Request $request)
    {
        $vendor = Auth::user()->vendor;
        
        if (!$vendor) {
            abort(403, 'No vendor profile associated');
        }
        
        $query = $this->getVendorOrdersQuery($vendor);
        
        // Filter by status
        if ($request->filled('status')) {
            $statusField = $vendor->type . '_status';
            $query->where($statusField, $request->status);
        }
        
        $orders = $query->with('customer')
            ->orderBy('order_date', 'desc')
            ->paginate(20);
        
        return view('vendors.orders', compact('vendor', 'orders'));
    }
    
    /**
     * Update order status
     */
    public function updateStatus(Request $request, Order $order)
    {
        $vendor = Auth::user()->vendor;
        
        $request->validate([
            'status' => 'required|in:pending,in_progress,completed',
            'notes' => 'nullable|string',
        ]);
        
        // Verify vendor has permission
        $vendorField = $vendor->type . '_vendor_id';
        if ($order->$vendorField != $vendor->id) {
            return back()->with('error', '❌ You are not assigned to this order');
        }
        
        $statusField = $vendor->type . '_status';
        $dateField = $vendor->type . '_received_date';
        $oldStatus = $order->$statusField;
        
        // Update status
        $order->update([
            $statusField => $request->status,
            $dateField => $request->status == 'completed' ? now() : null,
        ]);
        
        // Create history
        \App\Models\OrderStatusHistory::create([
            'order_id' => $order->id,
            'stage' => $vendor->type,
            'old_status' => $oldStatus,
            'new_status' => $request->status,
            'notes' => $request->notes ?? "Status updated by vendor: {$vendor->name}",
            'updated_by' => Auth::id(),
        ]);
        
        return back()->with('success', '✅ Status updated successfully!');
    }
    
    /**
     * Get vendor orders query
     */
    protected function getVendorOrdersQuery($vendor)
    {
        $vendorField = $vendor->type . '_vendor_id';
        
        return Order::where($vendorField, $vendor->id)
            ->whereNotIn('order_status', ['cancelled', 'refunded']);
    }
    
    /**
     * Get vendor orders
     */
    protected function getVendorOrders($vendor)
    {
        return $this->getVendorOrdersQuery($vendor)->get();
    }
    
    /**
     * Calculate stats
     */
    protected function calculateStats($vendor, $orders)
    {
        $statusField = $vendor->type . '_status';
        
        return [
            'total_orders' => $orders->count(),
            'pending' => $orders->where($statusField, 'pending')->count(),
            'in_progress' => $orders->where($statusField, 'in_progress')->count(),
            'completed' => $orders->where($statusField, 'completed')->count(),
        ];
    }
}