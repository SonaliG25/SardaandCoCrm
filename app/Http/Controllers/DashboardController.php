<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Customer;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Overview Statistics (excluding cancelled/refunded)
        $stats = [
            'total_orders' => Order::whereNotIn('order_status', ['cancelled', 'refunded'])->count(),
            'new_orders' => Order::where('order_status', 'new')->count(),
            'processing_orders' => Order::where('order_status', 'processing')->count(),
            'dispatched_orders' => Order::where('order_status', 'dispatched')->count(),
            'delivered_orders' => Order::where('order_status', 'delivered')->count(),
            'total_revenue' => Order::whereNotIn('order_status', ['cancelled', 'refunded'])->sum('amount'),
            'pending_payment' => Order::whereIn('payment_status', ['pending', 'partial'])
                ->whereNotIn('order_status', ['cancelled', 'refunded'])
                ->sum('amount'),
            'total_customers' => Customer::count(),
        ];

        // Orders by Stage (excluding cancelled)
        $stageStats = [
            'dye_pending' => Order::where('dye_status', 'pending')
                ->whereNotIn('order_status', ['cancelled', 'refunded'])
                ->count(),
            'print_pending' => Order::where('print_status', 'pending')
                ->whereNotIn('order_status', ['cancelled', 'refunded'])
                ->count(),
            'emb_pending' => Order::where('emb_status', 'pending')
                ->whereNotIn('order_status', ['cancelled', 'refunded'])
                ->count(),
            'master_pending' => Order::where('master_status', 'pending')
                ->whereNotIn('order_status', ['cancelled', 'refunded'])
                ->count(),
            'ready_to_dispatch' => Order::where('master_status', 'completed')
                ->where('shipping_status', 'pending')
                ->whereNotIn('order_status', ['cancelled', 'refunded'])
                ->count(),
        ];

        // Recent Orders (last 10, excluding cancelled/refunded)
        $recentOrders = Order::with(['customer', 'shippingPartner'])
            ->whereNotIn('order_status', ['cancelled', 'refunded'])
            ->latest('order_date')
            ->take(10)
            ->get();

        // Orders by Date (Last 7 days) - excluding cancelled
        $ordersByDate = Order::select(
                DB::raw('DATE(order_date) as date'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(amount) as revenue')
            )
            ->where('order_date', '>=', now()->subDays(7))
            ->whereNotIn('order_status', ['cancelled', 'refunded'])
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // If no data in last 7 days, create dummy data for visualization
        if ($ordersByDate->isEmpty()) {
            $ordersByDate = collect();
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $count = Order::whereDate('order_date', $date->format('Y-m-d'))
                    ->whereNotIn('order_status', ['cancelled', 'refunded'])
                    ->count();
                $revenue = Order::whereDate('order_date', $date->format('Y-m-d'))
                    ->whereNotIn('order_status', ['cancelled', 'refunded'])
                    ->sum('amount');
                
                $ordersByDate->push((object)[
                    'date' => $date->format('Y-m-d'),
                    'count' => $count,
                    'revenue' => $revenue
                ]);
            }
        }

        // Payment Status Distribution (excluding cancelled)
        $paymentStats = Order::select('payment_status', DB::raw('COUNT(*) as count'))
            ->whereNotIn('order_status', ['cancelled', 'refunded'])
            ->groupBy('payment_status')
            ->get()
            ->pluck('count', 'payment_status');

        // Shipping Status Distribution (excluding cancelled)
        $shippingStats = Order::select('shipping_status', DB::raw('COUNT(*) as count'))
            ->where('shipping_status', '!=', 'pending')
            ->whereNotIn('order_status', ['cancelled', 'refunded'])
            ->groupBy('shipping_status')
            ->get()
            ->pluck('count', 'shipping_status');

        return view('dashboard.index', compact(
            'stats',
            'stageStats',
            'recentOrders',
            'ordersByDate',
            'paymentStats',
            'shippingStats'
        ));
    }

    /**
     * Get dashboard stats for AJAX refresh
     */
    public function stats()
    {
        return response()->json([
            'total_orders' => Order::whereNotIn('order_status', ['cancelled', 'refunded'])->count(),
            'new_orders' => Order::where('order_status', 'new')->count(),
            'processing_orders' => Order::where('order_status', 'processing')->count(),
            'pending_payment' => Order::whereIn('payment_status', ['pending', 'partial'])
                ->whereNotIn('order_status', ['cancelled', 'refunded'])
                ->sum('amount'),
            'ready_to_dispatch' => Order::where('master_status', 'completed')
                ->where('shipping_status', 'pending')
                ->whereNotIn('order_status', ['cancelled', 'refunded'])
                ->count(),
        ]);
    }
}