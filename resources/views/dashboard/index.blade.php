@extends('layouts.app')

@section('title', 'Dashboard')

@section('page-title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Welcome back, {{ Auth::user()->name }}!</h1>
            <p class="text-gray-600 mt-1">Here's what's happening with your orders today.</p>
        </div>
        <div class="mt-4 md:mt-0 flex items-center space-x-3">
            <!-- <a href="{{ route('orders.export') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium rounded-lg shadow-sm transition">
                <i class="fas fa-download mr-2"></i>
                Export Data
            </a>
            <a href="{{ route('orders.create') }}" class="inline-flex items-center px-4 py-2 bg-sarda-600 hover:bg-sarda-700 text-white font-medium rounded-lg shadow-lg hover:shadow-xl transition-all transform hover:scale-105">
                <i class="fas fa-plus mr-2"></i>
                New Order
            </a> -->
        </div>
    </div>

    <!-- Quick Stats Overview -->
    <div class="bg-gradient-to-r from-sarda-600 to-sarda-700 rounded-lg shadow-lg p-6 text-white">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="text-center">
                <div class="text-3xl font-bold mb-1">{{ $stats['total_orders'] }}</div>
                <div class="text-sarda-100 text-sm">Total Orders</div>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold mb-1">{{ $stats['total_customers'] }}</div>
                <div class="text-sarda-100 text-sm">Total Customers</div>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold mb-1">&#8377;{{ number_format($stats['total_revenue'], 0) }}</div>
                <div class="text-sarda-100 text-sm">Total Revenue</div>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold mb-1">{{ $stageStats['ready_to_dispatch'] }}</div>
                <div class="text-sarda-100 text-sm">Ready to Ship</div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- New Orders -->
        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500 hover:shadow-lg transition-all transform hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1 font-medium">New Orders</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['new_orders'] }}</p>
                    <p class="text-xs text-green-600 mt-2">
                        <i class="fas fa-arrow-up"></i> Needs attention
                    </p>
                </div>
                <div class="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-plus-circle text-blue-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Processing Orders -->
        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-yellow-500 hover:shadow-lg transition-all transform hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1 font-medium">Processing</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['processing_orders'] }}</p>
                    <p class="text-xs text-gray-500 mt-2">
                        <i class="fas fa-clock"></i> In progress
                    </p>
                </div>
                <div class="w-14 h-14 bg-yellow-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-spinner text-yellow-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Dispatched Orders -->
        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-purple-500 hover:shadow-lg transition-all transform hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1 font-medium">Dispatched</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['dispatched_orders'] }}</p>
                    <p class="text-xs text-purple-600 mt-2">
                        <i class="fas fa-truck"></i> In transit
                    </p>
                </div>
                <div class="w-14 h-14 bg-purple-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-shipping-fast text-purple-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Delivered Orders -->
        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500 hover:shadow-lg transition-all transform hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1 font-medium">Delivered</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['delivered_orders'] }}</p>
                    <p class="text-xs text-green-600 mt-2">
                        <i class="fas fa-check-circle"></i> Completed
                    </p>
                </div>
                <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-check-double text-green-600 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

  <!-- Order Trends Chart -->
<!-- <div class="lg:col-span-2 bg-white rounded-lg shadow-sm p-6">
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-semibold text-gray-900">
            <i class="fas fa-chart-line text-sarda-600 mr-2"></i>
            Order Trends (Last 7 Days)
        </h3>
        <div class="flex space-x-2">
            <button class="px-3 py-1 text-xs font-medium text-white bg-sarda-600 rounded shadow">7D</button>
            <button class="px-3 py-1 text-xs font-medium text-gray-600 hover:bg-gray-100 rounded">30D</button>
            <button class="px-3 py-1 text-xs font-medium text-gray-600 hover:bg-gray-100 rounded">90D</button>
        </div>
    </div>
    
    @if($ordersByDate->count() > 0 && $ordersByDate->sum('count') > 0)
        Chart with Data 
        <div class="space-y-4">
            @foreach($ordersByDate as $order)
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700">{{ \Carbon\Carbon::parse($order->date)->format('M d, D') }}</span>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-600">
                            <i class="fas fa-shopping-cart text-sarda-500 mr-1"></i>
                            {{ $order->count }} {{ $order->count == 1 ? 'order' : 'orders' }}
                        </span>
                        <span class="text-sm font-semibold text-sarda-600">â‚¹{{ number_format($order->revenue, 0) }}</span>
                    </div>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-4 shadow-inner">
                    @php
                        $maxCount = $ordersByDate->max('count');
                        $percentage = $maxCount > 0 ? ($order->count / $maxCount) * 100 : 0;
                    @endphp
                    <div class="bg-gradient-to-r from-sarda-500 to-sarda-600 h-4 rounded-full shadow-sm transition-all duration-500 flex items-center justify-end pr-2" 
                         style="width: {{ $percentage }}%">
                        @if($percentage > 15)
                            <span class="text-xs font-bold text-white">{{ $order->count }}</span>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>

       Summary 
        <div class="mt-6 pt-6 border-t border-gray-200">
            <div class="grid grid-cols-3 gap-4">
                <div class="text-center">
                    <div class="text-sm text-gray-600 mb-1">Total Orders</div>
                    <div class="text-2xl font-bold text-gray-900">{{ $ordersByDate->sum('count') }}</div>
                </div>
                <div class="text-center">
                    <div class="text-sm text-gray-600 mb-1">Total Revenue</div>
                    <div class="text-2xl font-bold text-sarda-600">â‚¹{{ number_format($ordersByDate->sum('revenue'), 0) }}</div>
                </div>
                <div class="text-center">
                    <div class="text-sm text-gray-600 mb-1">Avg. Order Value</div>
                    <div class="text-2xl font-bold text-gray-900">
                        â‚¹{{ $ordersByDate->sum('count') > 0 ? number_format($ordersByDate->sum('revenue') / $ordersByDate->sum('count'), 0) : 0 }}
                    </div>
                </div>
            </div>
        </div>
    @else
      No Data State 
        <div class="text-center py-12">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
                <i class="fas fa-chart-line text-3xl text-gray-400"></i>
            </div>
            <h4 class="text-lg font-semibold text-gray-900 mb-2">No Order Data</h4>
            <p class="text-sm text-gray-600 mb-4">There are no orders in the last 7 days</p>
            <a href="{{ route('orders.create') }}" class="inline-flex items-center px-4 py-2 bg-sarda-600 hover:bg-sarda-700 text-white font-medium rounded-lg shadow transition">
                <i class="fas fa-plus mr-2"></i>
                Create Your First Order
            </a>
        </div>
    @endif
</div> -->

    <!-- Workflow & Payment Status Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Workflow Stages -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-tasks text-sarda-600 mr-2"></i>
                Workflow Stages
            </h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors cursor-pointer">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center text-white mr-3">
                            <i class="fas fa-tint"></i>
                        </div>
                        <span class="text-sm font-medium text-gray-700">Dye Pending</span>
                    </div>
                    <span class="bg-blue-600 text-white text-xs font-bold px-3 py-1 rounded-full">
                        {{ $stageStats['dye_pending'] }}
                    </span>
                </div>

                <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg hover:bg-green-100 transition-colors cursor-pointer">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center text-white mr-3">
                            <i class="fas fa-print"></i>
                        </div>
                        <span class="text-sm font-medium text-gray-700">Print Pending</span>
                    </div>
                    <span class="bg-green-600 text-white text-xs font-bold px-3 py-1 rounded-full">
                        {{ $stageStats['print_pending'] }}
                    </span>
                </div>

                <div class="flex items-center justify-between p-3 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors cursor-pointer">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center text-white mr-3">
                            <i class="fas fa-cut"></i>
                        </div>
                        <span class="text-sm font-medium text-gray-700">Emb Pending</span>
                    </div>
                    <span class="bg-purple-600 text-white text-xs font-bold px-3 py-1 rounded-full">
                        {{ $stageStats['emb_pending'] }}
                    </span>
                </div>

                <div class="flex items-center justify-between p-3 bg-orange-50 rounded-lg hover:bg-orange-100 transition-colors cursor-pointer">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-orange-500 rounded-lg flex items-center justify-center text-white mr-3">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <span class="text-sm font-medium text-gray-700">Master Pending</span>
                    </div>
                    <span class="bg-orange-600 text-white text-xs font-bold px-3 py-1 rounded-full">
                        {{ $stageStats['master_pending'] }}
                    </span>
                </div>

                <div class="flex items-center justify-between p-3 bg-gradient-to-r from-sarda-500 to-sarda-600 rounded-lg shadow-lg">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center text-sarda-600 mr-3">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <span class="text-sm font-medium text-white">Ready to Dispatch</span>
                    </div>
                    <span class="bg-white text-sarda-700 text-xs font-bold px-3 py-1 rounded-full">
                        {{ $stageStats['ready_to_dispatch'] }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Payment Status Distribution -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-credit-card text-green-600 mr-2"></i>
                Payment Status
            </h3>
            <div class="space-y-4">
                @php
                    $paymentColors = [
                        'pending' => ['bg' => 'bg-yellow-500', 'text' => 'text-yellow-700', 'light' => 'bg-yellow-50'],
                        'partial' => ['bg' => 'bg-blue-500', 'text' => 'text-blue-700', 'light' => 'bg-blue-50'],
                        'received' => ['bg' => 'bg-green-500', 'text' => 'text-green-700', 'light' => 'bg-green-50'],
                        'remittance_balance' => ['bg' => 'bg-red-500', 'text' => 'text-red-700', 'light' => 'bg-red-50'],
                    ];
                @endphp

                @foreach($paymentStats as $status => $count)
                @php
                    $color = $paymentColors[$status] ?? ['bg' => 'bg-gray-500', 'text' => 'text-gray-700', 'light' => 'bg-gray-50'];
                    $percentage = $stats['total_orders'] > 0 ? ($count / $stats['total_orders']) * 100 : 0;
                @endphp
                <div class="p-3 {{ $color['light'] }} rounded-lg">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-medium {{ $color['text'] }} capitalize">
                            {{ str_replace('_', ' ', $status) }}
                        </span>
                        <span class="text-sm font-bold text-gray-900">{{ $count }}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="{{ $color['bg'] }} h-2 rounded-full transition-all duration-500" 
                             style="width: {{ $percentage }}%"></div>
                    </div>
                    <div class="text-xs text-gray-600 mt-1 text-right">{{ number_format($percentage, 1) }}%</div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Shipping Status -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-shipping-fast text-blue-600 mr-2"></i>
                Shipping Status
            </h3>
            <div class="space-y-3">
                @php
                    $shippingIcons = [
                        'dispatched' => ['icon' => 'fa-box', 'color' => 'blue'],
                        'in_transit' => ['icon' => 'fa-truck', 'color' => 'yellow'],
                        'out_for_delivery' => ['icon' => 'fa-people-carry', 'color' => 'orange'],
                        'delivered' => ['icon' => 'fa-check-circle', 'color' => 'green'],
                        'failed' => ['icon' => 'fa-times-circle', 'color' => 'red'],
                    ];
                @endphp

                @foreach($shippingStats as $status => $count)
                @php
                    $shipping = $shippingIcons[$status] ?? ['icon' => 'fa-circle', 'color' => 'gray'];
                @endphp
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                    <div class="flex items-center">
                        <i class="fas {{ $shipping['icon'] }} text-{{ $shipping['color'] }}-500 mr-3"></i>
                        <span class="text-sm text-gray-700 capitalize">
                            {{ str_replace('_', ' ', $status) }}
                        </span>
                    </div>
                    <span class="bg-{{ $shipping['color'] }}-100 text-{{ $shipping['color'] }}-700 text-xs font-semibold px-2.5 py-1 rounded-full">
                        {{ $count }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Recent Orders Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between bg-gradient-to-r from-gray-50 to-white">
            <h3 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-history text-sarda-600 mr-2"></i>
                Recent Orders
            </h3>
            <a href="{{ route('orders.index') }}" class="text-sm text-sarda-600 hover:text-sarda-800 font-medium flex items-center">
                View All Orders <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                       <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order Details</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>                        
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Workflow</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Razorpay</th> 
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shipping</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($recentOrders as $order)
                   <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $order->order_date->format('d M Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $order->order_date->diffForHumans() }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    
                                    @if($order->product_image)
                                      <img src="{{ str_starts_with($order->product_image, 'http') ? $order->product_image : asset('storage/' . $order->product_image) }}"
                                         alt="Product" 
                                         class="w-12 h-12 rounded object-cover mr-3">
                                         
                                    @else
                                        <div class="w-12 h-12 bg-gray-200 rounded flex items-center justify-center mr-3">
                                            <i class="fas fa-image text-gray-400"></i>
                                        </div>
                                    @endif
                                    
                                    
                                    <div>
                                        <div class="flex items-center space-x-2">
                                            <span class="text-sm font-bold text-gray-900">{{ $order->woocommerce_order_id }}</span>
                                            @if($order->woocommerce_order_id)
                                                <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full" title="WooCommerce Order #{{ $order->woocommerce_order_id }}">
                                                    <i class="fas fa-shopping-cart"></i> WC
                                                </span>
                                            @endif
                                        </div>
                                        @if($order->awb_number)
                                            <div class="text-xs text-gray-500 mt-1">AWB: {{ $order->awb_number }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded-full bg-sarda-100 flex items-center justify-center text-sarda-700 font-semibold mr-2">
                                        {{ strtoupper(substr($order->customer->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $order->customer->name }}</div>
                                        @if($order->customer->phone)
                                            <div class="text-xs text-gray-500">{{ $order->customer->phone }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900">₹{{ number_format($order->amount, 0) }}</div>
                                @if($order->paid_amount > 0)
                                    <div class="text-xs text-green-600">Paid: ₹{{ number_format($order->paid_amount, 0) }}</div>
                                @endif
                            </td>
                           <td class="px-6 py-4 whitespace-nowrap">
    <div class="w-24">
        <div class="flex items-center justify-between text-xs text-gray-600 mb-1">
            <span class="font-medium
                @if($order->workflow_progress == 100) text-green-600
                @elseif($order->workflow_progress >= 50) text-sarda-600
                @else text-gray-500
                @endif">
                {{ number_format($order->workflow_progress, 0) }}%
            </span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2">
            <div class="h-2 rounded-full transition-all
                @if($order->workflow_progress == 100) bg-green-500
                @else bg-gradient-to-r from-sarda-500 to-sarda-600
                @endif"
                 style="width: {{ $order->workflow_progress }}%"></div>
        </div>
    </div>
</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @if($order->order_status == 'delivered') bg-green-100 text-green-800
                                    @elseif($order->order_status == 'dispatched') bg-blue-100 text-blue-800
                                    @elseif($order->order_status == 'processing') bg-yellow-100 text-yellow-800
                                    @elseif($order->order_status == 'new') bg-purple-100 text-purple-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    <i class="fas fa-circle mr-1 text-xs"></i>
                                    {{ ucfirst($order->order_status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @if($order->payment_status == 'received') bg-green-100 text-green-800
                                    @elseif($order->payment_status == 'partial') bg-yellow-100 text-yellow-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ ucfirst(str_replace('_', ' ', $order->payment_status)) }}
                                </span>
                            </td>
                             <!-- Payment Gateway & Razorpay Status Column -->
<td class="px-6 py-4 whitespace-nowrap">
    <div id="razorpay-status-{{ $order->id }}">
        
        <!-- Step 1: Show Payment Gateway Badge -->
        @if($order->payment_gateway)
            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $order->payment_gateway_badge }} mb-2">
                @if($order->payment_gateway == 'razorpay')
                    <i class="fas fa-credit-card mr-1"></i> Razorpay
                @elseif($order->payment_gateway == 'cod')
                    <i class="fas fa-money-bill mr-1"></i> COD
                @elseif($order->payment_gateway == 'bank_transfer')
                    <i class="fas fa-university mr-1"></i> Bank Transfer
                @elseif($order->payment_gateway == 'cheque')
                    <i class="fas fa-file-invoice mr-1"></i> Cheque
                @else
                    <i class="fas fa-question-circle mr-1"></i> {{ ucfirst($order->payment_gateway) }}
                @endif
            </span>
        @else
            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                <i class="fas fa-question mr-1"></i> Unknown
            </span>
        @endif

        <!-- Step 2: Show Razorpay Details (ONLY if gateway is Razorpay) -->
        @if($order->payment_gateway == 'razorpay')
            
            @if($order->razorpay_payment_status)
                {{-- Payment status found from Razorpay --}}
                @php
                    $status = $order->razorpay_payment_status;
                    $statusText = match($status) {
                        'captured' => 'Payment Successful',
                        'authorized' => 'Payment Processing',
                        'created' => 'Payment Initiated',
                        'failed' => 'Payment Failed',
                        default => 'Pending',
                    };
                @endphp
                
                <div class="mt-2">
                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                        @if($status == 'captured') bg-green-100 text-green-800
                        @elseif($status == 'authorized') bg-blue-100 text-blue-800
                        @elseif($status == 'created') bg-yellow-100 text-yellow-800
                        @elseif($status == 'failed') bg-red-100 text-red-800
                        @else bg-gray-100 text-gray-800
                        @endif">
                        @if($status == 'captured')
                            <i class="fas fa-check-circle mr-1"></i>
                        @elseif($status == 'failed')
                            <i class="fas fa-times-circle mr-1"></i>
                        @else
                            <i class="fas fa-clock mr-1"></i>
                        @endif
                        {{ $statusText }}
                    </span>
                    
                    @if($order->razorpay_payment_method)
                        <div class="text-xs text-gray-600 mt-1">
                            <i class="fas fa-wallet mr-1"></i>{{ ucfirst($order->razorpay_payment_method) }}
                        </div>
                    @endif
                    
                    @if($order->razorpay_amount)
                        <div class="text-xs text-gray-700 font-medium mt-1">
                            ₹{{ number_format($order->razorpay_amount, 0) }}
                        </div>
                    @endif
                    
                    @if($order->razorpay_checked_at)
                        <div class="text-xs text-gray-400 mt-1">
                            {{ \Carbon\Carbon::parse($order->razorpay_checked_at)->diffForHumans() }}
                        </div>
                    @endif
                </div>
                
                {{-- Show Verified or Refresh button --}}
                @if(in_array($status, ['captured', 'authorized']))
                    <div class="text-xs text-green-600 mt-2 flex items-center">
                        <i class="fas fa-shield-check mr-1"></i>
                        <span>Verified</span>
                    </div>
                @else
                    <button onclick="checkPayment({{ $order->id }})" 
                            id="check-btn-{{ $order->id }}"
                            class="text-xs text-indigo-600 hover:text-indigo-900 mt-2 flex items-center">
                        <i class="fas fa-sync-alt mr-1" id="check-icon-{{ $order->id }}"></i>
                        <span id="check-text-{{ $order->id }}">Check Again</span>
                    </button>
                @endif
                
            @else
                {{-- Payment not checked yet --}}
                <div class="text-xs text-orange-600 mt-2 mb-2 flex items-center">
                    <i class="fas fa-exclamation-circle mr-1"></i>
                    <span>Not checked</span>
                </div>
                
                <button onclick="checkPayment({{ $order->id }})" 
                        id="check-btn-{{ $order->id }}"
                        class="text-xs text-indigo-600 hover:text-indigo-900 flex items-center">
                    <i class="fas fa-search-dollar mr-1" id="check-icon-{{ $order->id }}"></i>
                    <span id="check-text-{{ $order->id }}">Check Payment</span>
                </button>
            @endif
            
        @endif
        
        {{-- For non-Razorpay payments, show nothing --}}
        
    </div>
</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($order->shippingPartner)
                                    <div class="text-sm text-gray-900">{{ $order->shippingPartner->name }}</div>
                                    <span class="text-xs text-gray-500 capitalize">{{ str_replace('_', ' ', $order->shipping_status) }}</span>
                                @else
                                    <span class="text-xs text-gray-400">Not assigned</span>
                                @endif
                            </td>
                           <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="{{ route('orders.show', $order) }}" class="text-sarda-600 hover:text-sarda-900 font-medium">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </td>
                        </tr>
                    @empty  
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-4 text-gray-300"></i>
                            <p class="text-lg font-medium">No orders found</p>
                            <p class="text-sm mt-2">Create your first order to get started</p>
                            <a href="{{ route('orders.create') }}" class="inline-flex items-center mt-4 px-4 py-2 bg-sarda-600 hover:bg-sarda-700 text-white font-medium rounded-lg shadow transition">
                                <i class="fas fa-plus mr-2"></i>
                                Create Order
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <a href="{{ route('orders.index', ['status' => 'new']) }}" class="bg-white rounded-lg shadow-sm p-6 hover:shadow-lg transition-all transform hover:-translate-y-1 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-sm font-medium text-gray-600 mb-1">New Orders</h4>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['new_orders'] }}</p>
                    <p class="text-xs text-blue-600 mt-2">Click to view all</p>
                </div>
                <i class="fas fa-arrow-right text-2xl text-blue-500"></i>
            </div>
        </a>

        <a href="{{ route('orders.index', ['payment_status' => 'pending']) }}" class="bg-white rounded-lg shadow-sm p-6 hover:shadow-lg transition-all transform hover:-translate-y-1 border-l-4 border-red-500">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-sm font-medium text-gray-600 mb-1">Pending Payments</h4>
                    <p class="text-2xl font-bold text-gray-900">&#8377;{{ number_format($stats['pending_payment'], 0) }}</p>
                    <p class="text-xs text-red-600 mt-2">Click to view all</p>
                </div>
                <i class="fas fa-arrow-right text-2xl text-red-500"></i>
            </div>
        </a>

        <a href="{{ route('vendors.index') }}" class="bg-white rounded-lg shadow-sm p-6 hover:shadow-lg transition-all transform hover:-translate-y-1 border-l-4 border-sarda-500">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-sm font-medium text-gray-600 mb-1">Manage Vendors</h4>
                    <p class="text-2xl font-bold text-gray-900">View All</p>
                    <p class="text-xs text-sarda-600 mt-2">Click to manage</p>
                </div>
                <i class="fas fa-arrow-right text-2xl text-sarda-500"></i>
            </div>
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto-refresh stats every 60 seconds
    setInterval(function() {
        fetch('{{ route("dashboard.stats") }}')
            .then(response => response.json())
            .then(data => {
                console.log('Dashboard stats refreshed', data);
                // You can update specific elements here if needed
            })
            .catch(error => console.error('Error refreshing stats:', error));
    }, 60000);

    // Add smooth scroll to tables
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
</script>
@endpush