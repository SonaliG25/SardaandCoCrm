<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Customer;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Sales Report
     */
    public function salesReport(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        
        // Summary Stats
        $stats = [
            'total_orders' => Order::whereBetween('order_date', [$startDate, $endDate])
                ->whereNotIn('order_status', ['cancelled', 'refunded'])
                ->count(),
            
            'total_revenue' => Order::whereBetween('order_date', [$startDate, $endDate])
                ->whereNotIn('order_status', ['cancelled', 'refunded'])
                ->sum('amount'),
            
            'paid_revenue' => Order::whereBetween('order_date', [$startDate, $endDate])
                ->where('payment_status', 'received')
                ->whereNotIn('order_status', ['cancelled', 'refunded'])
                ->sum('amount'),
            
            'pending_revenue' => Order::whereBetween('order_date', [$startDate, $endDate])
                ->whereIn('payment_status', ['pending', 'partial'])
                ->whereNotIn('order_status', ['cancelled', 'refunded'])
                ->sum('amount'),
            
            'average_order_value' => 0,
            
            'delivered_orders' => Order::whereBetween('order_date', [$startDate, $endDate])
                ->where('order_status', 'delivered')
                ->count(),
        ];
        
        if ($stats['total_orders'] > 0) {
            $stats['average_order_value'] = $stats['total_revenue'] / $stats['total_orders'];
        }
        
        // Daily Sales Trend
        $dailySales = Order::select(
                DB::raw('DATE(order_date) as date'),
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(amount) as revenue')
            )
            ->whereBetween('order_date', [$startDate, $endDate])
            ->whereNotIn('order_status', ['cancelled', 'refunded'])
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();
        
        // Sales by Payment Method
        $paymentMethodSales = Order::select(
                'payment_gateway',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(amount) as revenue')
            )
            ->whereBetween('order_date', [$startDate, $endDate])
            ->whereNotIn('order_status', ['cancelled', 'refunded'])
            ->groupBy('payment_gateway')
            ->get();
        
        // Top Customers
        $topCustomers = Order::select(
                'customer_id',
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(amount) as total_spent')
            )
            ->with('customer')
            ->whereBetween('order_date', [$startDate, $endDate])
            ->whereNotIn('order_status', ['cancelled', 'refunded'])
            ->groupBy('customer_id')
            ->orderBy('total_spent', 'desc')
            ->limit(10)
            ->get();
        
        return view('reports.sales', compact(
            'stats',
            'dailySales',
            'paymentMethodSales',
            'topCustomers',
            'startDate',
            'endDate'
        ));
    }
    
    /**
     * Payment Report
     */
    public function paymentReport(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        
        // Payment Summary
        $stats = [
            'total_receivable' => Order::whereBetween('order_date', [$startDate, $endDate])
                ->whereNotIn('order_status', ['cancelled', 'refunded'])
                ->sum('amount'),
            
            'total_received' => Order::whereBetween('order_date', [$startDate, $endDate])
                ->where('payment_status', 'received')
                ->sum('paid_amount'),
            
            'total_pending' => Order::whereBetween('order_date', [$startDate, $endDate])
                ->whereIn('payment_status', ['pending', 'partial'])
                ->whereNotIn('order_status', ['cancelled', 'refunded'])
                ->sum('amount'),
            
            'razorpay_payments' => Order::whereBetween('order_date', [$startDate, $endDate])
                ->where('payment_gateway', 'razorpay')
                ->where('payment_status', 'received')
                ->sum('paid_amount'),
            
            'cod_payments' => Order::whereBetween('order_date', [$startDate, $endDate])
                ->where('payment_gateway', 'cod')
                ->where('payment_status', 'received')
                ->sum('paid_amount'),
        ];
        
        $stats['collection_rate'] = $stats['total_receivable'] > 0 
            ? ($stats['total_received'] / $stats['total_receivable']) * 100 
            : 0;
        
        // Payment Status Breakdown
        $paymentStatusBreakdown = Order::select(
                'payment_status',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(amount) as total')
            )
            ->whereBetween('order_date', [$startDate, $endDate])
            ->whereNotIn('order_status', ['cancelled', 'refunded'])
            ->groupBy('payment_status')
            ->get();
        
        // Payment Gateway Breakdown
        $gatewayBreakdown = Order::select(
                'payment_gateway',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(paid_amount) as total')
            )
            ->whereBetween('order_date', [$startDate, $endDate])
            ->where('payment_status', 'received')
            ->groupBy('payment_gateway')
            ->get();
        
        // Pending Payments List
        $pendingPayments = Order::with('customer')
            ->whereBetween('order_date', [$startDate, $endDate])
            ->whereIn('payment_status', ['pending', 'partial'])
            ->whereNotIn('order_status', ['cancelled', 'refunded'])
            ->orderBy('order_date', 'desc')
            ->get();
        
        // Daily Collection Trend
        $dailyCollections = Order::select(
                DB::raw('DATE(order_date) as date'),
                DB::raw('SUM(paid_amount) as collected')
            )
            ->whereBetween('order_date', [$startDate, $endDate])
            ->where('payment_status', 'received')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();
        
        return view('reports.payment', compact(
            'stats',
            'paymentStatusBreakdown',
            'gatewayBreakdown',
            'pendingPayments',
            'dailyCollections',
            'startDate',
            'endDate'
        ));
    }
    
    /**
     * Vendor Performance Report
     */
    public function vendorPerformance(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        
        // Get all vendors with their performance metrics
        $vendors = Vendor::with(['dyeOrders', 'printOrders', 'embOrders'])
            ->where('is_active', true)
            ->get()
            ->map(function($vendor) use ($startDate, $endDate) {
                // Dye Performance
                $dyeOrders = Order::where('dye_vendor_id', $vendor->id)
                    ->whereBetween('order_date', [$startDate, $endDate])
                    ->get();
                
                $dyeCompleted = $dyeOrders->where('dye_status', 'completed')->count();
                $dyeTotal = $dyeOrders->count();
                $dyeOnTime = $dyeOrders->filter(function($order) {
                    return $order->dye_status == 'completed' && 
                           $order->dye_received_date && 
                           $order->dye_received_date <= $order->order_date->addDays(2);
                })->count();
                
                // Print Performance
                $printOrders = Order::where('print_vendor_id', $vendor->id)
                    ->whereBetween('order_date', [$startDate, $endDate])
                    ->get();
                
                $printCompleted = $printOrders->where('print_status', 'completed')->count();
                $printTotal = $printOrders->count();
                $printOnTime = $printOrders->filter(function($order) {
                    return $order->print_status == 'completed' && 
                           $order->print_received_date && 
                           $order->print_received_date <= $order->order_date->addDays(3);
                })->count();
                
                // Embroidery Performance
                $embOrders = Order::where('emb_vendor_id', $vendor->id)
                    ->whereBetween('order_date', [$startDate, $endDate])
                    ->get();
                
                $embCompleted = $embOrders->where('emb_status', 'completed')->count();
                $embTotal = $embOrders->count();
                $embOnTime = $embOrders->filter(function($order) {
                    return $order->emb_status == 'completed' && 
                           $order->emb_received_date && 
                           $order->emb_received_date <= $order->order_date->addDays(4);
                })->count();
                
                $totalOrders = $dyeTotal + $printTotal + $embTotal;
                $totalCompleted = $dyeCompleted + $printCompleted + $embCompleted;
                $totalOnTime = $dyeOnTime + $printOnTime + $embOnTime;
                
                return [
                    'vendor' => $vendor,
                    'total_orders' => $totalOrders,
                    'completed_orders' => $totalCompleted,
                    'completion_rate' => $totalOrders > 0 ? ($totalCompleted / $totalOrders) * 100 : 0,
                    'on_time_delivery' => $totalCompleted > 0 ? ($totalOnTime / $totalCompleted) * 100 : 0,
                    'dye' => [
                        'total' => $dyeTotal,
                        'completed' => $dyeCompleted,
                        'on_time' => $dyeOnTime,
                    ],
                    'print' => [
                        'total' => $printTotal,
                        'completed' => $printCompleted,
                        'on_time' => $printOnTime,
                    ],
                    'emb' => [
                        'total' => $embTotal,
                        'completed' => $embCompleted,
                        'on_time' => $embOnTime,
                    ],
                ];
            })
            ->sortByDesc('total_orders');
        
        return view('reports.vendor-performance', compact(
            'vendors',
            'startDate',
            'endDate'
        ));
    }
}