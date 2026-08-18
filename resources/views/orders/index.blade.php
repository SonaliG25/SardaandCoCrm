@extends('layouts.app')

@section('title', 'Orders')

@section('page-title', 'Orders Management')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Orders Management</h1>
            <p class="text-gray-600 mt-1">Manage and track all your orders</p>
        </div>
      <div class="flex items-center space-x-3">
          
    <!-- Check Razorpay Payments Button (NEW) -->
    <form action="{{ route('orders.check-all-payments') }}" method="POST" class="inline">
        @csrf
        <button type="submit" 
                class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-lg transition transform hover:scale-105">
            <i class="fas fa-rupee-sign mr-2"></i>
            Check Payments
        </button>
    </form>
    <!-- Quick Sync (Recent 20) -->
    <form action="{{ route('woocommerce.sync-orders') }}" method="POST" class="inline">
        @csrf
        <input type="hidden" name="limit" value="20">
        <input type="hidden" name="status" value="processing">
        <button type="submit" class="sync-btn inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg">
            <i class="fas fa-sync-alt mr-2"></i>
            Sync Recent
        </button>
    </form>

    <!-- Full Sync (All Orders) -->
    <!--<form action="{{ route('woocommerce.sync-orders') }}" method="POST" class="inline">-->
    <!--    @csrf-->
    <!--    <input type="hidden" name="limit" value="100">-->
    <!--    <input type="hidden" name="status" value="any">-->
    <!--    <button type="submit" class="sync-btn inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg">-->
    <!--        <i class="fas fa-cloud-download-alt mr-2"></i>-->
    <!--        Sync All Orders-->
    <!--    </button>-->
    <!--</form>-->
</div>
    </div>
<!-- Search and Filters -->
<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <form method="GET" action="{{ route('orders.index') }}" id="filterForm" class="space-y-4">
        <!-- Search Bar -->
        <div class="flex items-center space-x-3">
            <div class="flex-1">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Search by Order ID, Customer Name, Phone, Email, AWB, Product..." 
                           class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sarda-500 focus:border-sarda-500">
                </div>
            </div>
            <button type="submit" 
                    class="px-6 py-3 bg-sarda-600 hover:bg-sarda-700 text-white font-medium rounded-lg transition">
                <i class="fas fa-search mr-2"></i>
                Search
            </button>
            @if(request()->hasAny(['search', 'order_status', 'payment_status', 'shipping_status', 'source', 'date_from', 'date_to', 'stage']))
                <a href="{{ route('orders.index') }}" 
                   class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-lg transition">
                    <i class="fas fa-times mr-2"></i>
                    Clear
                </a>
            @endif
        </div>

        <!-- Advanced Filters (Collapsible) -->
        <div x-data="{ showFilters: {{ request()->hasAny(['order_status', 'payment_status', 'shipping_status', 'source', 'date_from', 'date_to', 'stage']) ? 'true' : 'false' }} }">
            <button type="button" 
                    @click="showFilters = !showFilters"
                    class="text-sm text-sarda-600 hover:text-sarda-700 font-medium flex items-center">
                <i class="fas mr-2" :class="showFilters ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                <span x-text="showFilters ? 'Hide Filters' : 'Show Filters'"></span>
            </button>

            <div x-show="showFilters" 
                 x-transition
                 class="mt-4 space-y-4">
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Order Status Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Order Status</label>
                        <select name="order_status" 
                                onchange="document.getElementById('filterForm').submit()"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sarda-500 focus:border-sarda-500">
                            <option value="">All Status</option>
                            <option value="new" {{ request('order_status') == 'new' ? 'selected' : '' }}>New</option>
                            <option value="processing" {{ request('order_status') == 'processing' ? 'selected' : '' }}>Processing</option>
                            <option value="dispatched" {{ request('order_status') == 'dispatched' ? 'selected' : '' }}>Dispatched</option>
                            <option value="delivered" {{ request('order_status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                            <option value="cancelled" {{ request('order_status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    <!-- Payment Status Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Payment Status</label>
                        <select name="payment_status" 
                                onchange="document.getElementById('filterForm').submit()"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sarda-500 focus:border-sarda-500">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>Partial</option>
                            <option value="received" {{ request('payment_status') == 'received' ? 'selected' : '' }}>Received</option>
                        </select>
                    </div>

                    <!-- Shipping Status Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Shipping Status</label>
                        <select name="shipping_status" 
                                onchange="document.getElementById('filterForm').submit()"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sarda-500 focus:border-sarda-500">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('shipping_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="dispatched" {{ request('shipping_status') == 'dispatched' ? 'selected' : '' }}>Dispatched</option>
                            <option value="in_transit" {{ request('shipping_status') == 'in_transit' ? 'selected' : '' }}>In Transit</option>
                            <option value="out_for_delivery" {{ request('shipping_status') == 'out_for_delivery' ? 'selected' : '' }}>Out for Delivery</option>
                            <option value="delivered" {{ request('shipping_status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                            <option value="failed" {{ request('shipping_status') == 'failed' ? 'selected' : '' }}>Failed</option>
                        </select>
                    </div>

                    <!-- Source Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Source</label>
                        <select name="source" 
                                onchange="document.getElementById('filterForm').submit()"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sarda-500 focus:border-sarda-500">
                            <option value="">All Sources</option>
                            <option value="woocommerce" {{ request('source') == 'woocommerce' ? 'selected' : '' }}>WooCommerce</option>
                            <option value="manual" {{ request('source') == 'manual' ? 'selected' : '' }}>Manual Entry</option>
                        </select>
                    </div>

                    <!-- Workflow Stage Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Workflow Stage</label>
                        <select name="stage" 
                                onchange="document.getElementById('filterForm').submit()"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sarda-500 focus:border-sarda-500">
                            <option value="">All Stages</option>
                            <option value="dye" {{ request('stage') == 'dye' ? 'selected' : '' }}>Pending Dye</option>
                            <option value="print" {{ request('stage') == 'print' ? 'selected' : '' }}>Pending Print</option>
                            <option value="emb" {{ request('stage') == 'emb' ? 'selected' : '' }}>Pending Embroidery</option>
                            <option value="master" {{ request('stage') == 'master' ? 'selected' : '' }}>Pending Master</option>
                            <option value="shipping" {{ request('stage') == 'shipping' ? 'selected' : '' }}>Ready to Ship</option>
                        </select>
                    </div>

                    <!-- Date From -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                        <input type="date" 
                               name="date_from" 
                               value="{{ request('date_from') }}"
                               onchange="document.getElementById('filterForm').submit()"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sarda-500 focus:border-sarda-500">
                    </div>

                    <!-- Date To -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                        <input type="date" 
                               name="date_to" 
                               value="{{ request('date_to') }}"
                               onchange="document.getElementById('filterForm').submit()"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sarda-500 focus:border-sarda-500">
                    </div>
                </div>

                <!-- Manual Apply Button (optional, since auto-submit is enabled) -->
                <div class="flex justify-end">
                    <button type="submit" 
                            class="px-6 py-2 bg-sarda-600 hover:bg-sarda-700 text-white font-medium rounded-lg transition">
                        <i class="fas fa-filter mr-2"></i>
                        Apply Filters
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

    <!-- Orders Table -->
    @if($orders->count() > 0)
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                   <thead class="bg-gray-50 sticky top-0 z-10">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order Details</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>                        
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <!--<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Workflow</th>-->
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Razorpay</th> 
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remark</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shipping</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($orders as $order)
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
                           <td class="px-6 py-4">
    <div class="flex items-center">
        <div class="w-8 h-8 rounded-full bg-sarda-100 flex items-center justify-center text-sarda-700 font-semibold mr-2 flex-shrink-0">
            {{ strtoupper(substr($order->customer->name, 0, 1)) }}
        </div>
        <div class="min-w-0">
          <p class="font-medium text-gray-900 break-words max-w-[150px]">{{ $order->customer->name }}</p>
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
                            <!--<td class="px-6 py-4 whitespace-nowrap">-->
                            <!--    <div class="w-24">-->
                            <!--        <div class="flex items-center justify-between text-xs text-gray-600 mb-1">-->
                            <!--            <span class="font-medium">{{ number_format($order->workflow_progress, 0) }}%</span>-->
                            <!--        </div>-->
                            <!--        <div class="w-full bg-gray-200 rounded-full h-2">-->
                            <!--            <div class="bg-gradient-to-r from-sarda-500 to-sarda-600 h-2 rounded-full transition-all" -->
                            <!--                 style="width: {{ $order->workflow_progress }}%"></div>-->
                            <!--        </div>-->
                            <!--    </div>-->
                            <!--</td>-->
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
        {{-- Payment found in Razorpay --}}
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
        
    @elseif($order->razorpay_checked_at)
        {{-- Checked but payment not found --}}
        <div class="mt-2">
            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                <i class="fas fa-exclamation-triangle mr-1"></i>
                Payment Not Found
            </span>
            <div class="text-xs text-gray-500 mt-1">
                Checked {{ \Carbon\Carbon::parse($order->razorpay_checked_at)->diffForHumans() }}
            </div>
        </div>
        
        <button onclick="checkPayment({{ $order->id }})" 
                id="check-btn-{{ $order->id }}"
                class="text-xs text-indigo-600 hover:text-indigo-900 mt-2 flex items-center">
            <i class="fas fa-sync-alt mr-1" id="check-icon-{{ $order->id }}"></i>
            <span id="check-text-{{ $order->id }}">Try Again</span>
        </button>
        
    @else
        {{-- Never checked --}}
        <div class="text-xs text-orange-600 mt-2 mb-2 flex items-center">
            <i class="fas fa-question-circle mr-1"></i>
            <span>Not checked yet</span>
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
<td class="px-4 py-3 text-sm text-gray-900">
    @if($order->remark)
        <div class="max-w-xs truncate" title="{{ $order->remark }}">
            <i class="fas fa-comment-dots text-yellow-600 mr-1"></i>
            {{ Str::limit($order->remark, 50) }}
        </div>
    @else
        <span class="text-gray-400 text-xs">—</span>
    @endif
</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($order->shippingPartner)
                                    <div class="text-sm text-gray-900">{{ $order->shippingPartner->name }}</div>
                                    <span class="text-xs text-gray-500 capitalize">{{ str_replace('_', ' ', $order->shipping_status) }}</span>
                                @else
                                    <span class="text-xs text-gray-400">Not assigned</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                   <a href="{{ route('orders.show', ['order' => $order, 'page' => request('page', 1)]) }}" 
                                           class="text-sarda-600 hover:text-sarda-900" 
                                           title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('orders.edit', array_merge(request()->all(), ['order' => $order, 'page' => request('page', 1)])) }}" 
                                           class="text-blue-600 hover:text-blue-900"
                                           title="Edit Order">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    <form action="{{ route('orders.destroy', $order) }}" 
                                          method="POST" 
                                          class="inline"
                                          onsubmit="return confirm('Are you sure you want to delete this order?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="text-red-600 hover:text-red-900"
                                                title="Delete Order">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($orders->hasPages())
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                {{ $orders->links() }}
            </div>
            @endif
        </div>
    @else
        <!-- Empty State -->
        <div class="bg-white rounded-lg shadow-sm p-12 text-center mt-4">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
                <i class="fas fa-inbox text-3xl text-gray-400"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No orders found</h3>
            <p class="text-gray-600 mb-6">
                @if(request()->hasAny(['search', 'order_status', 'payment_status', 'shipping_status', 'source', 'date_from', 'date_to', 'stage']))
                    No orders match your search criteria. Try adjusting your filters.
                @else
                    Get started by syncing orders from WooCommerce.
                @endif
            </p>
            
            <div class="flex items-center justify-center space-x-3">
                @if(request()->hasAny(['search', 'order_status', 'payment_status', 'shipping_status', 'source', 'date_from', 'date_to', 'stage']))
                    <a href="{{ route('orders.index') }}" 
                       class="inline-flex items-center px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded-lg transition">
                        <i class="fas fa-times mr-2"></i>
                        Clear Filters
                    </a>
                @endif
                
            </div>
        </div>
    @endif

    <!-- Quick Stats Summary -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-blue-500">
            <div class="text-sm text-gray-600">Total Orders</div>
            <div class="text-2xl font-bold text-gray-900">{{ $orders->total() }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-green-500">
            <div class="text-sm text-gray-600">Total Revenue</div>
            <div class="text-2xl font-bold text-gray-900">₹{{ number_format($orders->sum('amount'), 0) }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-yellow-500">
            <div class="text-sm text-gray-600">Avg Order Value</div>
            <div class="text-2xl font-bold text-gray-900">₹{{ $orders->count() > 0 ? number_format($orders->sum('amount') / $orders->count(), 0) : 0 }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-red-500">
            <div class="text-sm text-gray-600">Pending Payment</div>
            <div class="text-2xl font-bold text-gray-900">₹{{ number_format($orders->where('payment_status', 'pending')->sum('amount'), 0) }}</div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Show loading state when syncing
document.querySelectorAll('.sync-btn').forEach(button => {
    button.closest('form').addEventListener('submit', function(e) {
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Syncing...';
    });
});
</script>
@endpush
@push('scripts')
<script>
// Check single order payment status
function checkPayment(orderId) {
    const btn = document.getElementById(`check-btn-${orderId}`);
    const icon = document.getElementById(`check-icon-${orderId}`);
    const text = document.getElementById(`check-text-${orderId}`);
    const container = document.getElementById(`razorpay-status-${orderId}`);
    
    // Show loading state
    btn.disabled = true;
    icon.classList.add('fa-spin');
    text.textContent = 'Checking...';
    
    // Make AJAX request
    fetch(`/orders/${orderId}/check-payment`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update the entire Razorpay column content
            container.innerHTML = `
                <div class="text-xs">
                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                        ${getStatusClass(data.razorpay_status)}">
                        ${capitalizeFirst(data.razorpay_status)}
                    </span>
                    ${data.payment_method ? `<div class="text-xs text-gray-500 mt-1">${capitalizeFirst(data.payment_method)}</div>` : ''}
                    ${data.amount ? `<div class="text-xs text-gray-500">₹${Number(data.amount).toLocaleString()}</div>` : ''}
                    <div class="text-xs text-gray-400 mt-1">Just now</div>
                </div>
                <button onclick="checkPayment(${orderId})" 
                        class="text-xs text-green-600 hover:text-green-900 mt-2 flex items-center">
                    <i class="fas fa-check-circle mr-1"></i>
                    <span>Checked</span>
                </button>
            `;
            
            // Show success toast
            showToast('success', data.message);
            
            // Update payment status badge if needed
            updatePaymentBadge(orderId, data.payment_status);
        } else {
            // Show error and reset button
            icon.classList.remove('fa-spin');
            text.textContent = 'Retry';
            btn.disabled = false;
            showToast('error', data.message || 'Payment not found');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        icon.classList.remove('fa-spin');
        text.textContent = 'Retry';
        btn.disabled = false;
        showToast('error', 'Failed to check payment');
    });
}

// Helper: Get status badge color class
function getStatusClass(status) {
    const classes = {
        'captured': 'bg-green-100 text-green-800',
        'authorized': 'bg-blue-100 text-blue-800',
        'created': 'bg-yellow-100 text-yellow-800',
        'failed': 'bg-red-100 text-red-800',
        'refunded': 'bg-purple-100 text-purple-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
}

// Helper: Capitalize first letter
function capitalizeFirst(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

// Helper: Update payment status badge in table
function updatePaymentBadge(orderId, status) {
    // Find the payment status cell and update it
    const row = document.getElementById(`razorpay-status-${orderId}`).closest('tr');
    const paymentCell = row.querySelector('[data-payment-status]');
    if (paymentCell && status === 'received') {
        paymentCell.innerHTML = `
            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                Received
            </span>
        `;
    }
}

// Helper: Show toast notification
function showToast(type, message) {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg text-white z-50 ${
        type === 'success' ? 'bg-green-500' : 'bg-red-500'
    }`;
    toast.innerHTML = `
        <div class="flex items-center">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} mr-2"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Auto-submit form when date inputs change
document.querySelectorAll('input[name="date_from"], input[name="date_to"], select[name="shipping_status"]').forEach(element => {
    element.addEventListener('change', function() {
        this.closest('form').submit();
    });
});

// Show loading state when syncing
document.querySelectorAll('.sync-btn').forEach(button => {
    button.closest('form').addEventListener('submit', function(e) {
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Syncing...';
    });
});
</script>
<script>
// Check single order payment status
function checkPayment(orderId) {
    const btn = document.getElementById(`check-btn-${orderId}`);
    const icon = document.getElementById(`check-icon-${orderId}`);
    const text = document.getElementById(`check-text-${orderId}`);
    const container = document.getElementById(`razorpay-status-${orderId}`);
    
    // Show loading state
    btn.disabled = true;
    icon.classList.remove('fa-search-dollar', 'fa-sync-alt');
    icon.classList.add('fa-spinner', 'fa-spin');
    text.textContent = 'Checking...';
    
    // Make AJAX request
    fetch(`/orders/${orderId}/check-payment`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Get status badge class and text
            const statusInfo = getStatusInfo(data.razorpay_status);
            
            // Update the entire Razorpay column content
            container.innerHTML = `
                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800 mb-2">
                    <i class="fas fa-credit-card mr-1"></i> Razorpay
                </span>
                
                <div class="mt-2">
                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full ${statusInfo.badgeClass}">
                        <i class="fas ${statusInfo.icon} mr-1"></i>
                        ${statusInfo.text}
                    </span>
                    
                    ${data.payment_method ? `
                        <div class="text-xs text-gray-600 mt-1">
                            <i class="fas fa-wallet mr-1"></i>${capitalizeFirst(data.payment_method)}
                        </div>
                    ` : ''}
                    
                    ${data.amount ? `
                        <div class="text-xs text-gray-700 font-medium mt-1">
                            ₹${Number(data.amount).toLocaleString('en-IN')}
                        </div>
                    ` : ''}
                    
                    <div class="text-xs text-gray-400 mt-1">Just now</div>
                </div>
                
                ${statusInfo.verified ? `
                    <div class="text-xs text-green-600 mt-2 flex items-center">
                        <i class="fas fa-shield-check mr-1"></i>
                        <span>Verified</span>
                    </div>
                ` : `
                    <button onclick="checkPayment(${orderId})" 
                            class="text-xs text-indigo-600 hover:text-indigo-900 mt-2 flex items-center">
                        <i class="fas fa-sync-alt mr-1"></i>
                        <span>Check Again</span>
                    </button>
                `}
            `;
            
            // Show success toast
            showToast('success', data.message);
            
        } else {
            // Payment not found - update button to "Check Again"
            icon.classList.remove('fa-spinner', 'fa-spin');
            icon.classList.add('fa-sync-alt');
            text.textContent = 'Check Again';
            btn.disabled = false;
            
            showToast('error', data.message || 'Payment not found in Razorpay');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        
        // Reset button on error
        icon.classList.remove('fa-spinner', 'fa-spin');
        icon.classList.add('fa-sync-alt');
        text.textContent = 'Check Again';
        btn.disabled = false;
        
        showToast('error', 'Failed to check payment. Please try again.');
    });
}

// Helper: Get status info
function getStatusInfo(status) {
    const statusMap = {
        'captured': {
            text: 'Payment Successful',
            badgeClass: 'bg-green-100 text-green-800',
            icon: 'fa-check-circle',
            verified: true
        },
        'authorized': {
            text: 'Payment Processing',
            badgeClass: 'bg-blue-100 text-blue-800',
            icon: 'fa-clock',
            verified: true
        },
        'created': {
            text: 'Payment Initiated',
            badgeClass: 'bg-yellow-100 text-yellow-800',
            icon: 'fa-clock',
            verified: false
        },
        'failed': {
            text: 'Payment Failed',
            badgeClass: 'bg-red-100 text-red-800',
            icon: 'fa-times-circle',
            verified: false
        }
    };
    
    return statusMap[status] || {
        text: 'Pending',
        badgeClass: 'bg-gray-100 text-gray-800',
        icon: 'fa-clock',
        verified: false
    };
}

// Helper: Capitalize first letter
function capitalizeFirst(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

// Helper: Show toast notification
function showToast(type, message) {
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg text-white z-50 ${
        type === 'success' ? 'bg-green-500' : 'bg-red-500'
    }`;
    toast.innerHTML = `
        <div class="flex items-center">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} mr-2"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
</script>
@endpush