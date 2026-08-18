@extends('layouts.app')

@section('title', 'Customers')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Customers</h1>
            <p class="text-gray-600 mt-1">Manage your customer database</p>
        </div>
        <div class="flex items-center space-x-3">
            <button onclick="document.getElementById('sync-modal').classList.remove('hidden')"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                <i class="fas fa-sync mr-2"></i>
                Sync from WooCommerce
            </button>
            <a href="{{ route('customers.create') }}" 
               class="px-6 py-2 text-white font-medium rounded-lg"
               style="background: #f2601f;">
                <i class="fas fa-plus mr-2"></i>
                Add Customer
            </a>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
            <i class="fas fa-check-circle mr-2"></i>
            {!! session('success') !!}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
            <i class="fas fa-exclamation-circle mr-2"></i>
            {{ session('error') }}
        </div>
    @endif

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Total Customers</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_customers']) }}</p>
                </div>
                <i class="fas fa-users text-3xl text-blue-500"></i>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">With Orders</p>
                    <p class="text-2xl font-bold text-green-700">{{ number_format($stats['with_orders']) }}</p>
                </div>
                <i class="fas fa-shopping-cart text-3xl text-green-500"></i>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Without Orders</p>
                    <p class="text-2xl font-bold text-orange-700">{{ number_format($stats['without_orders']) }}</p>
                </div>
                <i class="fas fa-user-slash text-3xl text-orange-500"></i>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Total Revenue</p>
                    <p class="text-2xl font-bold text-purple-700">₹{{ number_format($stats['total_revenue']) }}</p>
                </div>
                <i class="fas fa-rupee-sign text-3xl text-purple-500"></i>
            </div>
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <form method="GET" action="{{ route('customers.index') }}" class="flex items-end space-x-4">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="Name, email, phone..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg">
            </div>
            
           <div>
    <label class="block text-sm font-medium text-gray-700 mb-2">Has Orders</label>
    <select name="has_orders" class="px-6 py-2 border border-gray-300 rounded-lg bg-white">
        <option value="">All</option>
        <option value="yes" {{ request('has_orders') == 'yes' ? 'selected' : '' }}>Yes</option>
        <option value="no" {{ request('has_orders') == 'no' ? 'selected' : '' }}>No</option>
    </select>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
    <select name="sort" class="px-3 py-2 border border-gray-300 rounded-lg bg-white">
        <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Latest</option>
        <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Name</option>
        <option value="orders" {{ request('sort') == 'orders' ? 'selected' : '' }}>Most Orders</option>
        <option value="spent" {{ request('sort') == 'spent' ? 'selected' : '' }}>Highest Spent</option>
    </select>
</div>

            <button type="submit" 
                    class="px-6 py-2 text-white rounded-lg"
                    style="background: #f2601f;">
                <i class="fas fa-filter mr-2"></i>
                Filter
            </button>

            @if(request()->hasAny(['search', 'has_orders', 'sort']))
                <a href="{{ route('customers.index') }}" 
                   class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg">
                    Clear
                </a>
            @endif
        </form>
    </div>

    <!-- Customers Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        @if($customers->count() > 0)
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Location</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Orders</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Spent</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($customers as $customer)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold"
                                 style="background: #f2601f;">
                                {{ substr($customer->name, 0, 1) }}
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">{{ $customer->name }}</p>
                                @if($customer->woocommerce_customer_id)
                                    <p class="text-xs text-gray-500">
                                        <i class="fab fa-wordpress text-blue-500"></i>
                                        WC: #{{ $customer->woocommerce_customer_id }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <p class="text-sm text-gray-900">{{ $customer->phone }}</p>
                        @if($customer->email)
                            <p class="text-xs text-gray-500">{{ $customer->email }}</p>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm text-gray-900">{{ $customer->city ?? 'N/A' }}</p>
                        @if($customer->state)
                            <p class="text-xs text-gray-500">{{ $customer->state }}, {{ $customer->pincode }}</p>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-3 py-1 text-xs font-semibold rounded-full
                            {{ $customer->orders_count > 0 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $customer->orders_count }} orders
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <p class="text-sm font-bold text-gray-900">₹{{ number_format($customer->total_spent ?? 0) }}</p>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <a href="{{ route('customers.show', $customer) }}" 
                           class="text-blue-600 hover:text-blue-900 mr-3">
                            <i class="fas fa-eye"></i> View
                        </a>
                        <a href="{{ route('customers.edit', $customer) }}" 
                           class="text-green-600 hover:text-green-900 mr-3">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t">
            {{ $customers->links() }}
        </div>
        @else
        <div class="text-center py-12">
            <i class="fas fa-users text-gray-400 text-5xl mb-3"></i>
            <p class="text-gray-600">No customers found</p>
            <a href="{{ route('customers.create') }}" class="text-blue-600 hover:underline mt-2 inline-block">
                Add your first customer
            </a>
        </div>
        @endif
    </div>
</div>

<!-- Sync Modal -->
<div id="sync-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Sync Customers from WooCommerce</h3>
        <p class="text-sm text-gray-600 mb-4">
            This will fetch all customers from WooCommerce and update your local database. 
            Existing customers will be updated.
        </p>
        
        <form method="POST" action="{{ route('customers.sync-woocommerce') }}">
            @csrf
            <div class="flex space-x-3">
                <button type="submit" 
                        class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                    <i class="fas fa-sync mr-2"></i>
                    Sync Now
                </button>
                <button type="button" 
                        onclick="document.getElementById('sync-modal').classList.add('hidden')"
                        class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-lg">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>
@endsection