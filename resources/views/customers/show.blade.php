@extends('layouts.app')

@section('title', 'Customer Details')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center space-x-3">
            <a href="{{ route('customers.index') }}" 
               class="text-gray-600 hover:text-gray-900">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ $customer->name }}</h1>
                <p class="text-gray-600 mt-1">Customer Details</p>
            </div>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('customers.edit', $customer) }}" 
               class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                <i class="fas fa-edit mr-2"></i>
                Edit
            </a>
        </div>
    </div>

    <!-- Success Message -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
            <i class="fas fa-check-circle mr-2"></i>
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Customer Info -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Customer Information -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-user mr-2" style="color: #f2601f;"></i>
                    Customer Information
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Full Name</p>
                        <p class="font-medium text-gray-900">{{ $customer->name }}</p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Phone</p>
                        <p class="font-medium text-gray-900">{{ $customer->phone }}</p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Email</p>
                        <p class="font-medium text-gray-900">{{ $customer->email ?? 'N/A' }}</p>
                    </div>
                    
                    @if($customer->woocommerce_customer_id)
                    <div>
                        <p class="text-sm text-gray-600 mb-1">WooCommerce ID</p>
                        <p class="font-medium text-blue-700">
                            <i class="fab fa-wordpress mr-1"></i>
                            #{{ $customer->woocommerce_customer_id }}
                        </p>
                    </div>
                    @endif
                    
                    @if($customer->address)
                    <div class="md:col-span-2">
                        <p class="text-sm text-gray-600 mb-1">Address</p>
                        <p class="font-medium text-gray-900">
                            {{ $customer->address }}
                            @if($customer->city || $customer->state || $customer->pincode)
                                <br>
                                {{ $customer->city }}
                                @if($customer->state), {{ $customer->state }}@endif
                                @if($customer->pincode) - {{ $customer->pincode }}@endif
                            @endif
                        </p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-shopping-cart mr-2" style="color: #f2601f;"></i>
                        Recent Orders
                    </h3>
                    @if($customer->orders->count() > 10)
                        <a href="{{ route('customers.orders', $customer) }}" 
                           class="text-sm text-blue-600 hover:text-blue-800">
                            View All →
                        </a>
                    @endif
                </div>

                @if($recentOrders->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($recentOrders as $order)
                            <tr>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                    #{{ $order->woocommerce_order_id }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    {{ $order->order_date->format('d M Y') }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full
                                        @if($order->order_status == 'delivered') bg-green-100 text-green-800
                                        @elseif($order->order_status == 'processing') bg-yellow-100 text-yellow-800
                                        @elseif($order->order_status == 'cancelled') bg-red-100 text-red-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ ucfirst($order->order_status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm font-bold text-gray-900">
                                    ₹{{ number_format($order->amount) }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <a href="{{ route('orders.show', $order) }}" 
                                       class="text-blue-600 hover:text-blue-900">
                                        View →
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-8">
                    <i class="fas fa-inbox text-gray-400 text-4xl mb-2"></i>
                    <p class="text-gray-600">No orders yet</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Right Column: Stats -->
        <div class="space-y-6">
            
            <!-- Stats Cards -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Statistics</h3>
                
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                        <div>
                            <p class="text-sm text-blue-600">Total Orders</p>
                            <p class="text-2xl font-bold text-blue-900">{{ $stats['total_orders'] }}</p>
                        </div>
                        <i class="fas fa-shopping-cart text-3xl text-blue-500"></i>
                    </div>

                    <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                        <div>
                            <p class="text-sm text-green-600">Total Spent</p>
                            <p class="text-2xl font-bold text-green-900">₹{{ number_format($stats['total_spent']) }}</p>
                        </div>
                        <i class="fas fa-rupee-sign text-3xl text-green-500"></i>
                    </div>

                    <div class="flex items-center justify-between p-3 bg-purple-50 rounded-lg">
                        <div>
                            <p class="text-sm text-purple-600">Avg Order Value</p>
                            <p class="text-2xl font-bold text-purple-900">₹{{ number_format($stats['avg_order_value']) }}</p>
                        </div>
                        <i class="fas fa-chart-line text-3xl text-purple-500"></i>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="p-3 bg-gray-50 rounded-lg">
                            <p class="text-xs text-gray-600">Pending</p>
                            <p class="text-lg font-bold text-gray-900">{{ $stats['pending_orders'] }}</p>
                        </div>
                        <div class="p-3 bg-gray-50 rounded-lg">
                            <p class="text-xs text-gray-600">Completed</p>
                            <p class="text-lg font-bold text-green-700">{{ $stats['completed_orders'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                
                <div class="space-y-2">
                    <a href="{{ route('customers.edit', $customer) }}" 
                       class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-center block">
                        <i class="fas fa-edit mr-2"></i>
                        Edit Customer
                    </a>
                    
                    @if($customer->orders->count() > 0)
                        <a href="{{ route('customers.orders', $customer) }}" 
                           class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-center block">
                            <i class="fas fa-list mr-2"></i>
                            View All Orders
                        </a>
                    @endif
                    
                    @if($customer->orders->count() == 0)
                        <form method="POST" 
                              action="{{ route('customers.destroy', $customer) }}"
                              onsubmit="return confirm('Are you sure you want to delete this customer?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg">
                                <i class="fas fa-trash mr-2"></i>
                                Delete Customer
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection