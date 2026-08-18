@extends('layouts.app')

@section('title', 'Order Details - #' . $order->order_id)

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header with Back Button -->
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center space-x-4">
            <a href="{{ route('orders.index', request()->all()) }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-lg transition">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Orders
            </a>
            <h1 class="text-2xl font-bold text-gray-900">Order #{{ $order->woocommerce_order_id }}</h1>
            <span class="px-3 py-1 text-sm font-semibold rounded-full
                @if($order->order_status == 'new') bg-blue-100 text-blue-800
                @elseif($order->order_status == 'processing') bg-yellow-100 text-yellow-800
                @elseif($order->order_status == 'dispatched') bg-purple-100 text-purple-800
                @elseif($order->order_status == 'delivered') bg-green-100 text-green-800
                @elseif($order->order_status == 'cancelled') bg-red-100 text-red-800
                @else bg-gray-100 text-gray-800
                @endif">
                {{ ucfirst($order->order_status) }}
            </span>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('orders.edit', $order) }}" 
               class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition">
                <i class="fas fa-edit mr-2"></i>
                Edit Order
            </a>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
            <i class="fas fa-check-circle mr-2"></i>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
            <i class="fas fa-exclamation-circle mr-2"></i>
            {{ session('error') }}
        </div>
    @endif

    @if(session('warning'))
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded-lg mb-6">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            {{ session('warning') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Main Details -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Customer Information -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-user text-sarda-600 mr-2"></i>
                    Customer Information
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Name</p>
                        <p class="font-medium text-gray-900">{{ $order->customer->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Phone</p>
                        <p class="font-medium text-gray-900">{{ $order->customer->phone }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Email</p>
                        <p class="font-medium text-gray-900">{{ $order->customer->email ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Pincode</p>
                        <p class="font-medium text-gray-900">{{ $order->customer->pincode ?? 'N/A' }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-sm text-gray-600">Address</p>
                        <p class="font-medium text-gray-900">{{ $order->customer->address }}</p>
                    </div>
                </div>
            </div>

            <!-- Product Information -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-box text-sarda-600 mr-2"></i>
                    Product Information
                </h3>
                <div class="flex items-start space-x-4">
                   
                    <div class="flex-1">
                        <p class="text-sm text-gray-600 mb-1">Description</p>
                        <p class="font-medium text-gray-900">{{ $order->product_description }}</p>
                        
                        <div class="mt-4 grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-600">Order Date</p>
                                <p class="font-medium text-gray-900">{{ $order->order_date->format('d M Y') }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">WooCommerce Order</p>
                                <p class="font-medium text-gray-900">#{{ $order->woocommerce_order_id }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

           
            <!-- Product-wise Workflow -->
<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
        <i class="fas fa-list-check text-orange-600 mr-2"></i>
        Product Workflow
    </h3>

    @forelse($order->products as $product)
    <div class="border border-gray-200 rounded-lg p-4 mb-4">
        <!-- Product Header -->
        <div class="flex items-start gap-4 mb-4 pb-4 border-b">
            @if($product->product_image)
            <img src="{{ $product->product_image }}" 
                 alt="{{ $product->product_name }}" 
                 class="w-16 h-16 object-cover rounded-lg border">
            @endif
            <div class="flex-1">
                <h4 class="font-semibold text-gray-900">{{ $product->product_name }}</h4>
                <p class="text-sm text-gray-600">Qty: {{ $product->quantity }} | ₹{{ number_format($product->price, 2) }}</p>
                @if($product->product_sku)
                <p class="text-xs text-gray-500">SKU: {{ $product->product_sku }}</p>
                @endif
            </div>
        </div>

        <!-- Workflow Stages -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Dye -->
            <div class="border-l-4 border-blue-500 pl-3">
                <p class="text-xs font-semibold text-blue-900 mb-1">
                    <i class="fas fa-droplet mr-1"></i>Dye
                </p>
                <p class="text-sm">
                    <span class="px-2 py-1 rounded text-xs font-medium
                        {{ $product->dye_status == 'completed' ? 'bg-green-100 text-green-800' : 
                           ($product->dye_status == 'in_progress' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                        {{ ucfirst(str_replace('_', ' ', $product->dye_status)) }}
                    </span>
                </p>
                @if($product->dyeVendor)
                <p class="text-xs text-gray-600 mt-1">{{ $product->dyeVendor->name }}</p>
                @endif
            </div>

            <!-- Print -->
            <div class="border-l-4 border-green-500 pl-3">
                <p class="text-xs font-semibold text-green-900 mb-1">
                    <i class="fas fa-print mr-1"></i>Print
                </p>
                <p class="text-sm">
                    <span class="px-2 py-1 rounded text-xs font-medium
                        {{ $product->print_status == 'completed' ? 'bg-green-100 text-green-800' : 
                           ($product->print_status == 'in_progress' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                        {{ ucfirst(str_replace('_', ' ', $product->print_status)) }}
                    </span>
                </p>
                @if($product->printVendor)
                <p class="text-xs text-gray-600 mt-1">{{ $product->printVendor->name }}</p>
                @endif
            </div>

            <!-- Embroidery -->
            <div class="border-l-4 border-purple-500 pl-3">
                <p class="text-xs font-semibold text-purple-900 mb-1">
                    <i class="fas fa-scissors mr-1"></i>Emb
                </p>
                <p class="text-sm">
                    <span class="px-2 py-1 rounded text-xs font-medium
                        {{ $product->emb_status == 'completed' ? 'bg-green-100 text-green-800' : 
                           ($product->emb_status == 'in_progress' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                        {{ ucfirst(str_replace('_', ' ', $product->emb_status)) }}
                    </span>
                </p>
                @if($product->embVendor)
                <p class="text-xs text-gray-600 mt-1">{{ $product->embVendor->name }}</p>
                @endif
            </div>

            <!-- Master -->
            <div class="border-l-4 border-orange-500 pl-3">
                <p class="text-xs font-semibold text-orange-900 mb-1">
                    <i class="fas fa-user-tie mr-1"></i>Master
                </p>
                <p class="text-sm">
                    <span class="px-2 py-1 rounded text-xs font-medium
                        {{ $product->master_status == 'completed' ? 'bg-green-100 text-green-800' : 
                           ($product->master_status == 'in_progress' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                        {{ ucfirst(str_replace('_', ' ', $product->master_status)) }}
                    </span>
                </p>
                @if($product->masterVendor)
                <p class="text-xs text-gray-600 mt-1">{{ $product->masterVendor->name }}</p>
                @endif
            </div>
        </div>
    </div>
    @empty
    <p class="text-gray-500 text-center py-4">No products found for this order.</p>
    @endforelse
</div>

            <!-- Delivery Tracking Section -->
  @if($order->awb_number)
<div class="bg-white rounded-lg shadow-sm p-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
            <i class="fas fa-truck text-sarda-600 mr-2"></i>
            Delivery Tracking
        </h3>
        
        <div class="flex items-center space-x-2">
            @if($order->shipping_status != 'delivered')
                <form action="{{ route('orders.refresh-tracking', $order) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" 
                            class="text-sm text-indigo-600 hover:text-indigo-900 flex items-center">
                        <i class="fas fa-sync-alt mr-1"></i>
                        Refresh Status
                    </button>
                </form>
                
                <!-- ✅ ADD CANCEL BUTTON HERE -->
                @if($order->shipping_status == 'dispatched' || $order->shipping_status == 'in_transit')
                <form action="{{ route('orders.cancel-shipment', $order) }}" 
                      method="POST" 
                      class="inline"
                      onsubmit="return confirm('Are you sure you want to cancel this shipment? This will remove the AWB and tracking data.')">
                    @csrf
                    <button type="submit" 
                            class="text-sm text-red-600 hover:text-red-900 flex items-center ml-3">
                        <i class="fas fa-times-circle mr-1"></i>
                        Cancel Shipment
                    </button>
                </form>
                @endif
            @endif
        </div>
    </div>
    
    <!-- Rest of tracking section... -->

                <!-- Tracking Header -->
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-4 mb-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <p class="text-xs text-gray-600 mb-1">AWB Number</p>
                            <p class="text-sm font-bold text-gray-900">{{ $order->awb_number }}</p>
                            @if($order->shippingPartner && $order->shippingPartner->tracking_url)
                                <a href="{{ $order->shippingPartner->tracking_url }}{{ $order->awb_number }}" 
                                   target="_blank"
                                   class="text-xs text-blue-600 hover:text-blue-800 flex items-center mt-1">
                                    <i class="fas fa-external-link-alt mr-1"></i>
                                    Track on {{ $order->shippingPartner->name }}
                                </a>
                            @endif
                        </div>
                        
                        <div>
                            <p class="text-xs text-gray-600 mb-1">Current Status</p>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold
                                @if($order->shipping_status == 'delivered') bg-green-100 text-green-800
                                @elseif($order->shipping_status == 'out_for_delivery') bg-blue-100 text-blue-800
                                @elseif($order->shipping_status == 'in_transit') bg-yellow-100 text-yellow-800
                                @elseif($order->shipping_status == 'dispatched') bg-purple-100 text-purple-800
                                @elseif($order->shipping_status == 'failed') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                @if($order->shipping_status == 'delivered')
                                    <i class="fas fa-check-circle mr-1"></i>
                                @elseif($order->shipping_status == 'out_for_delivery')
                                    <i class="fas fa-shipping-fast mr-1"></i>
                                @elseif($order->shipping_status == 'in_transit')
                                    <i class="fas fa-truck-moving mr-1"></i>
                                @elseif($order->shipping_status == 'dispatched')
                                    <i class="fas fa-box mr-1"></i>
                                @elseif($order->shipping_status == 'failed')
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                @else
                                    <i class="fas fa-clock mr-1"></i>
                                @endif
                                {{ ucfirst(str_replace('_', ' ', $order->shipping_status)) }}
                            </span>
                        </div>
                        
                        <div>
                            @if($order->delivered_date)
                                <p class="text-xs text-gray-600 mb-1">Delivered On</p>
                                <p class="text-sm font-bold text-green-700">
                                    {{ $order->delivered_date->format('d M Y, h:i A') }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ $order->delivered_date->diffForHumans() }}
                                </p>
                            @elseif($order->dispatched_date)
                                <p class="text-xs text-gray-600 mb-1">Dispatched On</p>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ $order->dispatched_date->format('d M Y, h:i A') }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ $order->dispatched_date->diffForHumans() }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Tracking Timeline -->
                @php
                    $trackingEvents = $order->trackingEvents;
                @endphp

                @if($trackingEvents && $trackingEvents->count() > 0)
                    <div class="relative mt-6">
                        <h4 class="text-sm font-semibold text-gray-700 mb-4">Tracking History</h4>
                        
                        <div class="space-y-4">
                            @foreach($trackingEvents as $event)
                                <div class="flex items-start relative">
                                    <!-- Timeline dot -->
                                    <div class="flex-shrink-0 w-3 h-3 rounded-full mt-1.5 z-10
                                        @if($loop->first) bg-green-500
                                        @else bg-gray-400
                                        @endif">
                                    </div>
                                    
                                    <!-- Timeline line -->
                                    @if(!$loop->last)
                                        <div class="absolute left-1.5 top-6 w-0.5 bg-gray-300" style="height: calc(100% + 1rem);"></div>
                                    @endif
                                    
                                    <!-- Event details -->
                                    <div class="ml-4 flex-1 pb-4">
                                        <div class="flex items-center justify-between mb-1">
                                            <p class="text-sm font-medium text-gray-900">{{ $event->status ?? 'Unknown Status' }}</p>
                                           <span class="text-xs text-gray-500">
                                                @if($event->tracked_at)
                                                    {{ \Carbon\Carbon::parse($event->tracked_at)->format('d M Y, h:i A') }}
                                                @else
                                                    N/A
                                                @endif
                                            </span>
                                        </div>
                                        
                                        @if($event->location)
                                            <p class="text-xs text-gray-600 flex items-center mt-1">
                                                <i class="fas fa-map-marker-alt mr-1"></i>
                                                {{ $event->location }}
                                            </p>
                                        @endif
                                        
                                        @if($event->remarks)
                                            <p class="text-xs text-gray-500 mt-1">{{ $event->remarks }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="text-center py-6 bg-gray-50 rounded-lg mt-6">
                        <i class="fas fa-info-circle text-gray-400 text-3xl mb-2"></i>
                        <p class="text-sm text-gray-600">No tracking events available yet</p>
                        <p class="text-xs text-gray-500 mt-1">Tracking updates will appear here once the shipment is scanned</p>
                    </div>
                @endif
            </div>
       @elseif(
    $order->products->count() > 0 && 
    $order->products->every(fn($p) => $p->master_status == 'completed') && 
    ($order->payment_gateway == 'cod' || $order->payment_status == 'received') &&
    !$order->awb_number &&
    !in_array($order->order_status, ['delivered', 'cancelled', 'refunded', 'dispatched'])
)
                <!-- Show Auto-Dispatch Button if not dispatched yet -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="text-center py-6">
                        <i class="fas fa-shipping-fast text-sarda-600 text-4xl mb-3"></i>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Ready to Ship</h3>
                        <p class="text-sm text-gray-600 mb-4">Production completed. This order is ready to be dispatched.</p>
                        
                        <form action="{{ route('orders.auto-dispatch', $order) }}" method="POST">
                            @csrf
                            <button type="submit" 
                                    class="inline-flex items-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg shadow-lg transition transform hover:scale-105">
                                <i class="fas fa-rocket mr-2"></i>
                                Auto-Dispatch with Delhivery
                            </button>
                        </form>
                        
                        <p class="text-xs text-gray-500 mt-3">
                            <i class="fas fa-info-circle mr-1"></i>
                            This will create a shipment in Delhivery and generate an AWB number
                        </p>
                    </div>
                </div>
            @endif

        </div>

        <!-- Right Column: Quick Info & Actions -->
        <div class="space-y-6">
            
            <!-- Payment Information -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-credit-card text-sarda-600 mr-2"></i>
                    Payment Details
                </h3>
                
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Total Amount</span>
                        <span class="text-lg font-bold text-gray-900">₹{{ number_format($order->amount, 2) }}</span>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Paid Amount</span>
                        <span class="font-medium text-gray-900">₹{{ number_format($order->paid_amount, 2) }}</span>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Payment Status</span>
                        <span class="px-3 py-1 text-xs font-semibold rounded-full
                            @if($order->payment_status == 'received') bg-green-100 text-green-800
                            @elseif($order->payment_status == 'partial') bg-yellow-100 text-yellow-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            {{ ucfirst($order->payment_status) }}
                        </span>
                    </div>
                    
                    @if($order->payment_gateway)
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Payment Method</span>
                        <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $order->payment_gateway_badge }}">
                            {{ ucfirst($order->payment_gateway) }}
                        </span>
                    </div>
                    @endif
                    
                    @if($order->razorpay_payment_status)
                    <div class="mt-4 pt-4 border-t">
                        <p class="text-xs text-gray-600 mb-2">Razorpay Details</p>
                        <div class="space-y-2">
                            <div class="flex justify-between text-xs">
                                <span class="text-gray-600">Status</span>
                                <span class="font-medium">{{ ucfirst($order->razorpay_payment_status) }}</span>
                            </div>
                            @if($order->razorpay_payment_method)
                            <div class="flex justify-between text-xs">
                                <span class="text-gray-600">Method</span>
                                <span class="font-medium">{{ ucfirst($order->razorpay_payment_method) }}</span>
                            </div>
                            @endif
                            @if($order->razorpay_payment_id)
                            <div class="text-xs">
                                <span class="text-gray-600">Payment ID</span>
                                <p class="font-mono text-gray-900 break-all">{{ $order->razorpay_payment_id }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
<!-- Add this after Product Description section -->
@if($order->remark)
<div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
    <div class="flex items-start">
        <i class="fas fa-comment-dots text-yellow-600 mt-1 mr-3"></i>
        <div class="flex-1">
            <h4 class="text-sm font-semibold text-yellow-800 mb-1">Remark</h4>
            <p class="text-sm text-yellow-700 whitespace-pre-wrap">{{ $order->remark }}</p>
        </div>
    </div>
</div>
@endif
            <!-- Shipping Information -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-shipping-fast text-sarda-600 mr-2"></i>
                    Shipping Info
                </h3>
                
                <div class="space-y-3">
                    @if($order->shippingPartner)
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Shipping Partner</p>
                        <p class="font-medium text-gray-900">{{ $order->shippingPartner->name }}</p>
                    </div>
                    @endif
                    
                    @if($order->awb_number)
                    <div>
                        <p class="text-sm text-gray-600 mb-1">AWB Number</p>
                        <p class="font-mono text-sm font-medium text-gray-900">{{ $order->awb_number }}</p>
                    </div>
                    @endif
                    
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Shipping Status</p>
                        <span class="px-3 py-1 text-xs font-semibold rounded-full
                            @if($order->shipping_status == 'delivered') bg-green-100 text-green-800
                            @elseif($order->shipping_status == 'dispatched') bg-purple-100 text-purple-800
                            @elseif($order->shipping_status == 'in_transit') bg-yellow-100 text-yellow-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            {{ ucfirst(str_replace('_', ' ', $order->shipping_status)) }}
                        </span>
                    </div>
                    
                    @if($order->dispatched_date)
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Dispatched Date</p>
                        <p class="text-sm text-gray-900">{{ $order->dispatched_date->format('d M Y, h:i A') }}</p>
                    </div>
                    @endif
                    
                    @if($order->delivered_date)
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Delivered Date</p>
                        <p class="text-sm font-medium text-green-700">{{ $order->delivered_date->format('d M Y, h:i A') }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                
                <div class="space-y-2">
                    <a href="{{ route('orders.edit', $order) }}" 
                       class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition text-center block">
                        <i class="fas fa-edit mr-2"></i>
                        Edit Order
                    </a>
                    
                    @if($order->payment_gateway == 'razorpay' && !$order->razorpay_payment_status)
                    <form action="{{ route('orders.check-payment', $order) }}" method="POST">
                        @csrf
                        <button type="submit" 
                                class="w-full px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition text-left">
                            <i class="fas fa-rupee-sign mr-2"></i>
                            Check Payment (Razorpay)
                        </button>
                    </form>
                    @endif
                    
                    @if($order->awb_number && $order->shipping_status != 'delivered')
                    <form action="{{ route('orders.refresh-tracking', $order) }}" method="POST">
                        @csrf
                        <button type="submit" 
                                class="w-full px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white font-medium rounded-lg transition text-left">
                            <i class="fas fa-sync-alt mr-2"></i>
                            Refresh Tracking
                        </button>
                    </form>
                    @endif
                    
                    <form action="{{ route('orders.destroy', $order) }}" 
                          method="POST" 
                          onsubmit="return confirm('Are you sure you want to delete this order?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition text-left">
                            <i class="fas fa-trash mr-2"></i>
                            Delete Order
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection