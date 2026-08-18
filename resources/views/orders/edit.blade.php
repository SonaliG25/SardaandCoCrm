@extends('layouts.app')

@section('title', 'Edit Order - ' . $order->order_id)

@section('page-title', 'Edit Order')

@section('content')
<div class="max-w-7xl mx-auto">
  <!-- Header -->
<div class="mb-6">
    <div class="flex items-center justify-between mb-2">
        <div class="flex items-center space-x-3">
            
            <!--<a href="{{ route('orders.index', $backParams ?? []) }}" class="w-full px-4 py-3 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium rounded-lg transition text-center block">-->
            <!--                <i class="fas fa-list mr-2"></i>-->
            <!--                Back to Orders-->
            <!--            </a>-->
            <a href="{{ route('orders.index', $backParams ?? []) }}" class="text-gray-600 hover:text-gray-900">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="text-2xl font-bold text-gray-900">Edit Order {{ $order->woocommerce_order_id }}</h1>
        </div>

                  @if($order->woocommerce_order_id)
            <form action="{{ route('orders.sync', $order) }}" method="POST" 
                  onsubmit="startSync(event, this)">
                @csrf
                <button type="submit" id="syncBtn"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition flex items-center gap-2 text-sm">
                    <i class="fas fa-sync-alt" id="syncIcon"></i>
                    <span id="syncText"> Sync from WooCommerce</span>
                </button>
            </form>
            @endif
    </div>
    <p class="text-gray-600">Update order details and workflow stages</p>
</div>

    <form action="{{ route('orders.update', $order) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column - Main Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Information -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="bg-gradient-to-r from-sarda-600 to-sarda-700 px-6 py-4">
                        <h3 class="text-lg font-semibold text-white">
                            <i class="fas fa-info-circle mr-2"></i>Basic Information
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Customer -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Customer <span class="text-red-500">*</span>
                                </label>
                                <select name="customer_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sarda-500 focus:border-sarda-500">
                                    <option value="">Select Customer</option>
                                    @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ $order->customer_id == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('customer_id')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Order Date -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Order Date <span class="text-red-500">*</span>
                                </label>
                                <input type="date" 
                                       name="order_date" 
                                       value="{{ old('order_date', $order->order_date->format('Y-m-d')) }}"
                                       required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sarda-500 focus:border-sarda-500">
                                @error('order_date')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Amount -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Order Amount <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2 text-gray-500">₹</span>
                                    <input type="number" 
                                           name="amount" 
                                           step="0.01" 
                                           value="{{ old('amount', $order->amount) }}"
                                           required
                                           class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sarda-500 focus:border-sarda-500">
                                </div>
                                @error('amount')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
    <label class="block text-sm font-medium text-gray-700 mb-2">
        Order Status <span class="text-red-500">*</span>
    </label>
    <select name="order_status" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sarda-500 focus:border-sarda-500">
          <option value="new"               {{ $order->order_status == 'new'               ? 'selected' : '' }}>New</option>
    <option value="processing"        {{ $order->order_status == 'processing'        ? 'selected' : '' }}>Processing</option>
    <option value="dispatched"        {{ $order->order_status == 'dispatched'        ? 'selected' : '' }}>Dispatched</option>
    <option value="delivered"         {{ $order->order_status == 'delivered'         ? 'selected' : '' }}>Delivered</option>
    <option value="cancelled"         {{ $order->order_status == 'cancelled'         ? 'selected' : '' }}>Cancelled</option>
    <option value="refunded"          {{ $order->order_status == 'refunded'          ? 'selected' : '' }}>Refunded</option>
    <option value="failed"            {{ $order->order_status == 'failed'            ? 'selected' : '' }}>Failed</option>
    <option value="rto_pay_pending"   {{ $order->order_status == 'rto_pay_pending'   ? 'selected' : '' }}>RTO Pay Pending</option>
    <option value="exchange_received" {{ $order->order_status == 'exchange_received' ? 'selected' : '' }}>Exchange Request Received</option>
    <option value="exchange_completed"{{ $order->order_status == 'exchange_completed'? 'selected' : '' }}>Exchange Request Completed</option>
    <option value="refund_requested"  {{ $order->order_status == 'refund_requested'  ? 'selected' : '' }}>Refund Requested</option>
    <option value="refund_approved"   {{ $order->order_status == 'refund_approved'   ? 'selected' : '' }}>Refund Approved</option>
    <option value="refund_cancelled"  {{ $order->order_status == 'refund_cancelled'  ? 'selected' : '' }}>Refund Cancelled</option>

    </select>
    @error('order_status')
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
    @enderror
</div>
                        </div>

                        <!-- Product Image -->
                        <!--<div>-->
                        <!--    <label class="block text-sm font-medium text-gray-700 mb-2">Product Image</label>-->
                        <!--    <div class="flex items-start space-x-4">-->
                        <!--        @if($order->product_image)-->
                        <!--        <div class="w-24 h-24 rounded-lg overflow-hidden border border-gray-200">-->
                        <!--            <img src="{{ $order->product_image_url }}" alt="Current Product" class="w-full h-full object-cover">-->
                        <!--        </div>-->
                        <!--        @endif-->
                        <!--        <div class="flex-1">-->
                        <!--            <input type="file" -->
                        <!--                   name="product_image" -->
                        <!--                   accept="image/*"-->
                        <!--                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sarda-500 focus:border-sarda-500">-->
                        <!--            <p class="text-xs text-gray-500 mt-1">Upload a new image to replace the current one (Max: 5MB)</p>-->
                        <!--        </div>-->
                        <!--    </div>-->
                        <!--    @error('product_image')-->
                        <!--        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>-->
                        <!--    @enderror-->
                        <!--</div>-->

                        <!-- Product Description -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Product Description</label>
                            <textarea name="product_description" 
                                      rows="3" 
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sarda-500 focus:border-sarda-500">{{ old('product_description', $order->product_description) }}</textarea>
                            @error('product_description')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Workflow Stages -->
<!--<div class="bg-white rounded-lg shadow-sm overflow-hidden">-->
<!--                    <div class="bg-gradient-to-r from-sarda-600 to-sarda-700 px-6 py-4">-->
<!--                        <h3 class="text-lg font-semibold text-white">-->
<!--                            <i class="fas fa-tasks mr-2"></i>Workflow Stages-->
<!--                        </h3>-->
<!--                    </div>-->
<!--</br>-->
    <!-- Dye Process -->
<!--    <div class="border-l-4 border-blue-500 pl-4 mb-6">-->
<!--        <h4 class="text-sm font-semibold text-blue-900 mb-3 flex items-center">-->
<!--            <i class="fas fa-droplet text-blue-600 mr-2"></i>-->
<!--            Dye Process-->
<!--        </h4>-->
        
<!--        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">-->
            <!-- Vendor -->
<!--            <div>-->
<!--                <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center justify-between">-->
<!--                    <span>Vendor</span>-->
<!--                    <button type="button" -->
<!--                            onclick="openVendorModal('dye')"-->
<!--                            class="px-2 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs transition">-->
<!--                        <i class="fas fa-plus"></i>-->
<!--                    </button>-->
<!--                </label>-->
<!--                <select name="dye_vendor_id" -->
<!--                        id="dye_vendor_id"-->
<!--                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sarda-500">-->
<!--                    <option value="">Select Vendor</option>-->
<!--                    @if(isset($vendors['dye']))-->
<!--                        @foreach($vendors['dye'] as $vendor)-->
<!--                            <option value="{{ $vendor->id }}" -->
<!--                                    {{ old('dye_vendor_id', $order->dye_vendor_id) == $vendor->id ? 'selected' : '' }}>-->
<!--                                {{ $vendor->name }}-->
<!--                            </option>-->
<!--                        @endforeach-->
<!--                    @endif-->
<!--                </select>-->
<!--            </div>-->
            
            <!-- Status -->
<!--            <div>-->
<!--                <label class="block text-sm font-medium text-gray-700 mb-2">Status <span class="text-red-500">*</span></label>-->
<!--                <select name="dye_status" -->
<!--                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sarda-500">-->
<!--                    <option value="pending" {{ old('dye_status', $order->dye_status) == 'pending' ? 'selected' : '' }}>Pending</option>-->
<!--                    <option value="in_progress" {{ old('dye_status', $order->dye_status) == 'in_progress' ? 'selected' : '' }}>In Progress</option>-->
<!--                    <option value="completed" {{ old('dye_status', $order->dye_status) == 'completed' ? 'selected' : '' }}>Completed</option>-->
<!--                </select>-->
<!--            </div>-->
            
            <!-- Received Date -->
<!--            <div>-->
<!--                <label class="block text-sm font-medium text-gray-700 mb-2">Received Date</label>-->
<!--                <input type="date" -->
<!--                       name="dye_received_date" -->
<!--                       value="{{ old('dye_received_date', $order->dye_received_date) }}"-->
<!--                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sarda-500">-->
<!--            </div>-->
<!--        </div>-->
<!--    </div>-->

    <!-- Print Process -->
<!--    <div class="border-l-4 border-green-500 pl-4 mb-6">-->
<!--        <h4 class="text-sm font-semibold text-green-900 mb-3 flex items-center">-->
<!--            <i class="fas fa-print text-green-600 mr-2"></i>-->
<!--            Print Process-->
<!--        </h4>-->
        
<!--        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">-->
            <!-- Vendor -->
<!--            <div>-->
<!--                <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center justify-between">-->
<!--                    <span>Vendor</span>-->
<!--                    <button type="button" -->
<!--                            onclick="openVendorModal('print')"-->
<!--                            class="px-2 py-1 bg-green-600 hover:bg-green-700 text-white rounded text-xs transition">-->
<!--                        <i class="fas fa-plus"></i>-->
<!--                    </button>-->
<!--                </label>-->
<!--                <select name="print_vendor_id" -->
<!--                        id="print_vendor_id"-->
<!--                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sarda-500">-->
<!--                    <option value="">Select Vendor</option>-->
<!--                    @if(isset($vendors['print']))-->
<!--                        @foreach($vendors['print'] as $vendor)-->
<!--                            <option value="{{ $vendor->id }}" -->
<!--                                    {{ old('print_vendor_id', $order->print_vendor_id) == $vendor->id ? 'selected' : '' }}>-->
<!--                                {{ $vendor->name }}-->
<!--                            </option>-->
<!--                        @endforeach-->
<!--                    @endif-->
<!--                </select>-->
<!--            </div>-->
            
            <!-- Status -->
<!--            <div>-->
<!--                <label class="block text-sm font-medium text-gray-700 mb-2">Status <span class="text-red-500">*</span></label>-->
<!--                <select name="print_status" -->
<!--                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sarda-500">-->
<!--                    <option value="pending" {{ old('print_status', $order->print_status) == 'pending' ? 'selected' : '' }}>Pending</option>-->
<!--                    <option value="in_progress" {{ old('print_status', $order->print_status) == 'in_progress' ? 'selected' : '' }}>In Progress</option>-->
<!--                    <option value="completed" {{ old('print_status', $order->print_status) == 'completed' ? 'selected' : '' }}>Completed</option>-->
<!--                </select>-->
<!--            </div>-->
            
            <!-- Received Date -->
<!--            <div>-->
<!--                <label class="block text-sm font-medium text-gray-700 mb-2">Received Date</label>-->
<!--                <input type="date" -->
<!--                       name="print_received_date" -->
<!--                       value="{{ old('print_received_date', $order->print_received_date) }}"-->
<!--                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sarda-500">-->
<!--            </div>-->
<!--        </div>-->
<!--    </div>-->

    <!-- Embroidery Process -->
<!--    <div class="border-l-4 border-purple-500 pl-4 mb-6">-->
<!--        <h4 class="text-sm font-semibold text-purple-900 mb-3 flex items-center">-->
<!--            <i class="fas fa-scissors text-purple-600 mr-2"></i>-->
<!--            Embroidery Process-->
<!--        </h4>-->
        
<!--        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">-->
            <!-- Vendor -->
<!--            <div>-->
<!--                <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center justify-between">-->
<!--                    <span>Vendor</span>-->
<!--                    <button type="button" -->
<!--                            onclick="openVendorModal('emb')"-->
<!--                            class="px-2 py-1 bg-purple-600 hover:bg-purple-700 text-white rounded text-xs transition">-->
<!--                        <i class="fas fa-plus"></i>-->
<!--                    </button>-->
<!--                </label>-->
<!--                <select name="emb_vendor_id" -->
<!--                        id="emb_vendor_id"-->
<!--                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sarda-500">-->
<!--                    <option value="">Select Vendor</option>-->
<!--                    @if(isset($vendors['emb']))-->
<!--                        @foreach($vendors['emb'] as $vendor)-->
<!--                            <option value="{{ $vendor->id }}" -->
<!--                                    {{ old('emb_vendor_id', $order->emb_vendor_id) == $vendor->id ? 'selected' : '' }}>-->
<!--                                {{ $vendor->name }}-->
<!--                            </option>-->
<!--                        @endforeach-->
<!--                    @endif-->
<!--                </select>-->
<!--            </div>-->
            
            <!-- Status -->
<!--            <div>-->
<!--                <label class="block text-sm font-medium text-gray-700 mb-2">Status <span class="text-red-500">*</span></label>-->
<!--                <select name="emb_status" -->
<!--                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sarda-500">-->
<!--                    <option value="pending" {{ old('emb_status', $order->emb_status) == 'pending' ? 'selected' : '' }}>Pending</option>-->
<!--                    <option value="in_progress" {{ old('emb_status', $order->emb_status) == 'in_progress' ? 'selected' : '' }}>In Progress</option>-->
<!--                    <option value="completed" {{ old('emb_status', $order->emb_status) == 'completed' ? 'selected' : '' }}>Completed</option>-->
<!--                </select>-->
<!--            </div>-->
            
            <!-- Received Date -->
<!--            <div>-->
<!--                <label class="block text-sm font-medium text-gray-700 mb-2">Received Date</label>-->
<!--                <input type="date" -->
<!--                       name="emb_received_date" -->
<!--                       value="{{ old('emb_received_date', $order->emb_received_date) }}"-->
<!--                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sarda-500">-->
<!--            </div>-->
<!--        </div>-->
<!--    </div>-->

    <!-- Master Process -->
<!--    <div class="border-l-4 border-orange-500 pl-4">-->
<!--        <h4 class="text-sm font-semibold text-orange-900 mb-3 flex items-center">-->
<!--            <i class="fas fa-user-tie text-orange-600 mr-2"></i>-->
<!--            Master Process-->
<!--        </h4>-->
        
<!--        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">-->
            <!-- Vendor -->
<!--            <div>-->
<!--                <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center justify-between">-->
<!--                    <span>Vendor</span>-->
<!--                    <button type="button" -->
<!--                            onclick="openVendorModal('master')"-->
<!--                            class="px-2 py-1 bg-orange-600 hover:bg-orange-700 text-white rounded text-xs transition">-->
<!--                        <i class="fas fa-plus"></i>-->
<!--                    </button>-->
<!--                </label>-->
<!--                <select name="master_vendor_id" -->
<!--                        id="master_vendor_id"-->
<!--                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sarda-500">-->
<!--                    <option value="">Select Vendor</option>-->
<!--                    @if(isset($vendors['master']))-->
<!--                        @foreach($vendors['master'] as $vendor)-->
<!--                            <option value="{{ $vendor->id }}" -->
<!--                                    {{ old('master_vendor_id', $order->master_vendor_id) == $vendor->id ? 'selected' : '' }}>-->
<!--                                {{ $vendor->name }}-->
<!--                            </option>-->
<!--                        @endforeach-->
<!--                    @endif-->
<!--                </select>-->
<!--            </div>-->
            
            <!-- Status -->
<!--            <div>-->
<!--                <label class="block text-sm font-medium text-gray-700 mb-2">Status <span class="text-red-500">*</span></label>-->
<!--                <select name="master_status" -->
<!--                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sarda-500">-->
<!--                    <option value="pending" {{ old('master_status', $order->master_status) == 'pending' ? 'selected' : '' }}>Pending</option>-->
<!--                    <option value="in_progress" {{ old('master_status', $order->master_status) == 'in_progress' ? 'selected' : '' }}>In Progress</option>-->
<!--                    <option value="completed" {{ old('master_status', $order->master_status) == 'completed' ? 'selected' : '' }}>Completed</option>-->
<!--                </select>-->
<!--            </div>-->
            
            <!-- Received Date -->
<!--            <div>-->
<!--                <label class="block text-sm font-medium text-gray-700 mb-2">Received Date</label>-->
<!--                <input type="date" -->
<!--                       name="master_received_date" -->
<!--                       value="{{ old('master_received_date', $order->master_received_date) }}"-->
<!--                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sarda-500">-->
<!--            </div>-->
<!--        </div>-->
<!--    </div>-->
<!--</div>-->
<!-- Product-wise Workflow Stages -->
<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
        <i class="fas fa-list-check text-orange-600 mr-2"></i>
        Product Workflow
    </h3>

    @forelse($order->products as $index => $product)
    <div class="border border-gray-200 rounded-lg p-5 mb-5 bg-gray-50">
        <!-- Product Header -->
        <div class="flex items-start gap-4 mb-5 pb-4 border-b border-gray-300">
            @if($product->product_image)
            <img src="{{ $product->product_image }}" 
                 alt="{{ $product->product_name }}" 
                 class="w-20 h-20 object-cover rounded-lg border-2 border-gray-200">
            @endif
            <div class="flex-1">
                <h4 class="font-bold text-gray-900 text-lg">{{ $product->product_name }}</h4>
                <p class="text-sm text-gray-600 mt-1">
                    <span class="font-medium">Qty:</span> {{ $product->quantity }} | 
                    <span class="font-medium">Price:</span> ₹{{ number_format($product->price, 2) }}
                </p>
                @if($product->product_sku)
                <p class="text-xs text-gray-500 mt-1">SKU: {{ $product->product_sku }}</p>
                @endif
            </div>
        </div>

        <input type="hidden" name="products[{{ $index }}][id]" value="{{ $product->id }}">

        <!-- Dye Process -->
        <div class="border-l-4 border-blue-500 pl-4 mb-5 bg-white p-4 rounded">
            <h4 class="text-sm font-semibold text-blue-900 mb-3 flex items-center">
                <i class="fas fa-droplet text-blue-600 mr-2"></i>
                Dye Process
            </h4>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Vendor -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1.5 flex items-center justify-between">
                        <span>Vendor</span>
                        <button type="button" 
                                onclick="openVendorModal('dye')"
                                class="px-2 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs transition">
                            <i class="fas fa-plus"></i>
                        </button>
                    </label>
                    <select name="products[{{ $index }}][dye_vendor_id]" 
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-sarda-500">
                        <option value="">Select Vendor</option>
                        @if(isset($vendors['dye']))
                            @foreach($vendors['dye'] as $vendor)
                                <option value="{{ $vendor->id }}" 
                                        {{ $product->dye_vendor_id == $vendor->id ? 'selected' : '' }}>
                                    {{ $vendor->name }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
                
                <!-- Status -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1.5">Status <span class="text-red-500">*</span></label>
                    <select name="products[{{ $index }}][dye_status]" 
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-sarda-500">
                        <option value="pending" {{ $product->dye_status == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="in_progress" {{ $product->dye_status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ $product->dye_status == 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>
                
                <!-- Received Date -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1.5">Received Date</label>
                    <input type="date" 
                           name="products[{{ $index }}][dye_received_date]" 
                           value="{{ $product->dye_received_date ? $product->dye_received_date->format('Y-m-d') : '' }}"
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-sarda-500">
                </div>
            </div>
        </div>

        <!-- Print Process -->
        <div class="border-l-4 border-green-500 pl-4 mb-5 bg-white p-4 rounded">
            <h4 class="text-sm font-semibold text-green-900 mb-3 flex items-center">
                <i class="fas fa-print text-green-600 mr-2"></i>
                Print Process
            </h4>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Vendor -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1.5 flex items-center justify-between">
                        <span>Vendor</span>
                        <button type="button" 
                                onclick="openVendorModal('print')"
                                class="px-2 py-1 bg-green-600 hover:bg-green-700 text-white rounded text-xs transition">
                            <i class="fas fa-plus"></i>
                        </button>
                    </label>
                    <select name="products[{{ $index }}][print_vendor_id]" 
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-sarda-500">
                        <option value="">Select Vendor</option>
                        @if(isset($vendors['print']))
                            @foreach($vendors['print'] as $vendor)
                                <option value="{{ $vendor->id }}" 
                                        {{ $product->print_vendor_id == $vendor->id ? 'selected' : '' }}>
                                    {{ $vendor->name }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
                
                <!-- Status -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1.5">Status <span class="text-red-500">*</span></label>
                    <select name="products[{{ $index }}][print_status]" 
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-sarda-500">
                        <option value="pending" {{ $product->print_status == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="in_progress" {{ $product->print_status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ $product->print_status == 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>
                
                <!-- Received Date -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1.5">Received Date</label>
                    <input type="date" 
                           name="products[{{ $index }}][print_received_date]" 
                           value="{{ $product->print_received_date ? $product->print_received_date->format('Y-m-d') : '' }}"
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-sarda-500">
                </div>
            </div>
        </div>

        <!-- Embroidery Process -->
        <div class="border-l-4 border-purple-500 pl-4 mb-5 bg-white p-4 rounded">
            <h4 class="text-sm font-semibold text-purple-900 mb-3 flex items-center">
                <i class="fas fa-scissors text-purple-600 mr-2"></i>
                Embroidery Process
            </h4>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Vendor -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1.5 flex items-center justify-between">
                        <span>Vendor</span>
                        <button type="button" 
                                onclick="openVendorModal('emb')"
                                class="px-2 py-1 bg-purple-600 hover:bg-purple-700 text-white rounded text-xs transition">
                            <i class="fas fa-plus"></i>
                        </button>
                    </label>
                    <select name="products[{{ $index }}][emb_vendor_id]" 
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-sarda-500">
                        <option value="">Select Vendor</option>
                        @if(isset($vendors['emb']))
                            @foreach($vendors['emb'] as $vendor)
                                <option value="{{ $vendor->id }}" 
                                        {{ $product->emb_vendor_id == $vendor->id ? 'selected' : '' }}>
                                    {{ $vendor->name }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
                
                <!-- Status -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1.5">Status <span class="text-red-500">*</span></label>
                    <select name="products[{{ $index }}][emb_status]" 
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-sarda-500">
                        <option value="pending" {{ $product->emb_status == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="in_progress" {{ $product->emb_status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ $product->emb_status == 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>
                
                <!-- Received Date -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1.5">Received Date</label>
                    <input type="date" 
                           name="products[{{ $index }}][emb_received_date]" 
                           value="{{ $product->emb_received_date ? $product->emb_received_date->format('Y-m-d') : '' }}"
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-sarda-500">
                </div>
            </div>
        </div>

        <!-- Master Process -->
        <div class="border-l-4 border-orange-500 pl-4 bg-white p-4 rounded">
            <h4 class="text-sm font-semibold text-orange-900 mb-3 flex items-center">
                <i class="fas fa-user-tie text-orange-600 mr-2"></i>
                Master Process
            </h4>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Vendor -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1.5 flex items-center justify-between">
                        <span>Vendor</span>
                        <button type="button" 
                                onclick="openVendorModal('master')"
                                class="px-2 py-1 bg-orange-600 hover:bg-orange-700 text-white rounded text-xs transition">
                            <i class="fas fa-plus"></i>
                        </button>
                    </label>
                    <select name="products[{{ $index }}][master_vendor_id]" 
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-sarda-500">
                        <option value="">Select Vendor</option>
                        @if(isset($vendors['master']))
                            @foreach($vendors['master'] as $vendor)
                                <option value="{{ $vendor->id }}" 
                                        {{ $product->master_vendor_id == $vendor->id ? 'selected' : '' }}>
                                    {{ $vendor->name }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
                
                <!-- Status -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1.5">Status <span class="text-red-500">*</span></label>
                    <select name="products[{{ $index }}][master_status]" 
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-sarda-500">
                        <option value="pending" {{ $product->master_status == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="in_progress" {{ $product->master_status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ $product->master_status == 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>
                
                <!-- Received Date -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1.5">Received Date</label>
                    <input type="date" 
                           name="products[{{ $index }}][master_received_date]" 
                           value="{{ $product->master_received_date ? $product->master_received_date->format('Y-m-d') : '' }}"
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-sarda-500">
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="text-center py-8 text-gray-500">
        <i class="fas fa-box-open text-4xl mb-3"></i>
        <p>No products found for this order.</p>
        <p class="text-sm">Products will be automatically synced from WooCommerce.</p>
    </div>
    @endforelse
</div>
                <!-- Shipping Information -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
                        <h3 class="text-lg font-semibold text-white">
                            <i class="fas fa-shipping-fast mr-2"></i>Shipping Information
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Shipping Partner</label>
                                <select name="shipping_partner_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sarda-500 focus:border-sarda-500">
                                    <option value="">Select Partner</option>
                                    @foreach($shippingPartners as $partner)
                                    <option value="{{ $partner->id }}" {{ $order->shipping_partner_id == $partner->id ? 'selected' : '' }}>
                                        {{ $partner->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">AWB Number</label>
                                <input type="text" 
                                       name="awb_number" 
                                       value="{{ old('awb_number', $order->awb_number) }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sarda-500 focus:border-sarda-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Dispatched Date</label>
                                <input type="date" 
                                       name="dispatched_date" 
                                       value="{{ old('dispatched_date', $order->dispatched_date?->format('Y-m-d')) }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sarda-500 focus:border-sarda-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Shipping Status <span class="text-red-500">*</span></label>
                                <select name="shipping_status" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sarda-500 focus:border-sarda-500">
                                    <option value="pending" {{ $order->shipping_status == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="dispatched" {{ $order->shipping_status == 'dispatched' ? 'selected' : '' }}>Dispatched</option>
                                    <option value="in_transit" {{ $order->shipping_status == 'in_transit' ? 'selected' : '' }}>In Transit</option>
                                    <option value="out_for_delivery" {{ $order->shipping_status == 'out_for_delivery' ? 'selected' : '' }}>Out for Delivery</option>
                                    <option value="delivered" {{ $order->shipping_status == 'delivered' ? 'selected' : '' }}>Delivered</option>
                                    <option value="failed" {{ $order->shipping_status == 'failed' ? 'selected' : '' }}>Failed</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Delivered Date</label>
                                <input type="date" 
                                       name="delivered_date" 
                                       value="{{ old('delivered_date', $order->delivered_date?->format('Y-m-d')) }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sarda-500 focus:border-sarda-500">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Payment & Actions -->
            <div class="space-y-6">
                <!-- Payment Information -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden top-6">
                    <div class="bg-gradient-to-r from-green-600 to-green-700 px-6 py-4">
                        <h3 class="text-lg font-semibold text-white">
                            <i class="fas fa-money-bill-wave mr-2"></i>Payment Information
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                          <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
            Payment Gateway <span class="text-red-500">*</span>
        </label>
        <select name="payment_gateway" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sarda-500 focus:border-sarda-500">
            <option value="razorpay" {{ $order->payment_gateway == 'razorpay' ? 'selected' : '' }}>Razorpay</option>
            <option value="cod" {{ $order->payment_gateway == 'cod' ? 'selected' : '' }}>Cash on Delivery (COD)</option>
            <option value="bank_transfer" {{ $order->payment_gateway == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
            <option value="cheque" {{ $order->payment_gateway == 'cheque' ? 'selected' : '' }}>Cheque</option>
            <option value="other" {{ $order->payment_gateway == 'other' ? 'selected' : '' }}>Other</option>
        </select>
    </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Payment Status <span class="text-red-500">*</span></label>
                            <select name="payment_status" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sarda-500 focus:border-sarda-500">
                                <option value="pending" {{ $order->payment_status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="partial" {{ $order->payment_status == 'partial' ? 'selected' : '' }}>Partial</option>
                                <option value="received" {{ $order->payment_status == 'received' ? 'selected' : '' }}>Received</option>
                                <option value="remittance_balance" {{ $order->payment_status == 'remittance_balance' ? 'selected' : '' }}>Remittance Balance</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Paid Amount</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-gray-500">₹</span>
                                <input type="number" 
                                       name="paid_amount" 
                                       step="0.01" 
                                       value="{{ old('paid_amount', $order->paid_amount) }}"
                                       class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sarda-500 focus:border-sarda-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Payment Notes</label>
                            <textarea name="payment_notes" 
                                      rows="3" 
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sarda-500 focus:border-sarda-500">{{ old('payment_notes', $order->payment_notes) }}</textarea>
                        </div>

                        <!-- Balance Display -->
                        <div class="pt-4 border-t">
                            <div class="flex justify-between items-center text-sm mb-2">
                                <span class="text-gray-600">Order Amount:</span>
                                <span class="font-semibold text-gray-900" id="orderAmountDisplay">₹{{ number_format($order->amount, 2) }}</span>
                            </div>
                            <div class="flex justify-between items-center text-sm mb-2">
                                <span class="text-gray-600">Paid Amount:</span>
                                <span class="font-semibold text-green-600" id="paidAmountDisplay">₹{{ number_format($order->paid_amount, 2) }}</span>
                            </div>
                            <div class="flex justify-between items-center pt-2 border-t">
                                <span class="font-semibold text-gray-700">Balance:</span>
                                <span class="font-bold text-red-600" id="balanceDisplay">₹{{ number_format($order->balance_amount, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ✅ ADD REMARK FIELD HERE -->
                <div class="mb-4">
                    
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-comment-dots text-sarda-600 mr-1"></i>
                        Remark
                     
                    </label>
                    <textarea name="remark" 
                              rows="3" 
                              placeholder="Add remark"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sarda-500 focus:border-transparent">{{ old('remark', $order->remark) }}</textarea>
                </div>
                <!-- Action Buttons -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="p-6 space-y-3">
                        <button type="submit" class="w-full px-4 py-3 bg-sarda-600 hover:bg-sarda-700 text-white font-semibold rounded-lg shadow-lg transition transform hover:scale-105">
                            <i class="fas fa-save mr-2"></i>
                            Update Order
                        </button>

                        <a href="{{ route('orders.show', $order) }}" class="w-full px-4 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition text-center block">
                            <i class="fas fa-times mr-2"></i>
                            Cancel
                        </a>

                       <a href="{{ route('orders.index', $backParams ?? []) }}" class="w-full px-4 py-3 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium rounded-lg transition text-center block">
                            <i class="fas fa-list mr-2"></i>
                            Back to Orders
                        </a>
                    </div>
                </div>

                <!-- Tips -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h4 class="font-semibold text-blue-900 mb-2 flex items-center">
                        <i class="fas fa-lightbulb mr-2"></i>
                        Quick Tips
                    </h4>
                    <ul class="text-sm text-blue-800 space-y-1">
                        <li>• Update workflow stages as they complete</li>
                        <li>• Assign AWB number when dispatching</li>
                        <li>• Mark payment status accurately</li>
                        <li>• Add notes for better tracking</li>
                    </ul>
                </div>
            </div>
        </div>
    </form>
</div>
<!-- Vendor Creation Modal -->
<div id="vendorModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
        <div class="flex items-center justify-between p-6 border-b">
            <h3 class="text-lg font-semibold text-gray-900">Add New Vendor</h3>
            <button type="button" onclick="closeVendorModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
    <!-- Scrollable Form Content -->
<form id="vendorForm" class="overflow-y-auto flex-1">
    <div class="p-4 space-y-3">
        @csrf
        <input type="hidden" id="vendor_type" name="type">
        
        <!-- Vendor Name -->
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">
                Vendor Name <span class="text-red-500">*</span>
            </label>
            <input type="text" 
                   id="vendor_name" 
                   name="name" 
                   required
                   class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                   placeholder="Enter vendor name">
        </div>
        
        <!-- Vendor Type -->
      <!-- Vendor Type -->
<div>
    <label class="block text-xs font-medium text-gray-700 mb-1">
        Vendor Type <span class="text-red-500">*</span>
    </label>
    <div class="flex gap-2">
        <label class="relative cursor-pointer flex-1">
            <input type="radio" 
                   name="type_radio" 
                   value="dye" 
                   class="peer sr-only"
                   onchange="document.getElementById('vendor_type').value='dye'">
            <div class="border-2 border-gray-300 rounded-lg py-2 px-3 text-center hover:border-blue-500 peer-checked:border-blue-600 peer-checked:bg-blue-600 peer-checked:text-white transition">
                <i class="fas fa-droplet text-blue-500 peer-checked:text-white text-lg block mb-0.5"></i>
                <p class="font-medium text-gray-900 peer-checked:text-white text-xs">Dye</p>
            </div>
        </label>
        
        <label class="relative cursor-pointer flex-1">
            <input type="radio" 
                   name="type_radio" 
                   value="print" 
                   class="peer sr-only"
                   onchange="document.getElementById('vendor_type').value='print'">
            <div class="border-2 border-gray-300 rounded-lg py-2 px-3 text-center hover:border-green-500 peer-checked:border-green-600 peer-checked:bg-green-600 peer-checked:text-white transition">
                <i class="fas fa-print text-green-500 peer-checked:text-white text-lg block mb-0.5"></i>
                <p class="font-medium text-gray-900 peer-checked:text-white text-xs">Print</p>
            </div>
        </label>
        
        <label class="relative cursor-pointer flex-1">
            <input type="radio" 
                   name="type_radio" 
                   value="emb" 
                   class="peer sr-only"
                   onchange="document.getElementById('vendor_type').value='emb'">
            <div class="border-2 border-gray-300 rounded-lg py-2 px-3 text-center hover:border-purple-500 peer-checked:border-purple-600 peer-checked:bg-purple-600 peer-checked:text-white transition">
                <i class="fas fa-scissors text-purple-500 peer-checked:text-white text-lg block mb-0.5"></i>
                <p class="font-medium text-gray-900 peer-checked:text-white text-xs">Emb</p>
            </div>
        </label>
        
        <label class="relative cursor-pointer flex-1">
            <input type="radio" 
                   name="type_radio" 
                   value="master" 
                   class="peer sr-only"
                   onchange="document.getElementById('vendor_type').value='master'">
            <div class="border-2 border-gray-300 rounded-lg py-2 px-3 text-center hover:border-orange-500 peer-checked:border-orange-600 peer-checked:bg-orange-600 peer-checked:text-white transition">
                <i class="fas fa-user-tie text-orange-500 peer-checked:text-white text-lg block mb-0.5"></i>
                <p class="font-medium text-gray-900 peer-checked:text-white text-xs">Master</p>
            </div>
        </label>
    </div>
</div>
        
        <!-- Contact Person -->
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">
                Contact Person
            </label>
            <input type="text" 
                   id="vendor_contact_person" 
                   name="contact_person"
                   class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                   placeholder="Enter contact person name">
        </div>
        
        <!-- Phone Number -->
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">
                Phone Number <span class="text-red-500">*</span>
            </label>
            <input type="tel" 
                   id="vendor_phone" 
                   name="phone" 
                   required
                   class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                   placeholder="+91 1234567890">
        </div>
        
        <!-- Email Address -->
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">
                Email Address
            </label>
            <input type="email" 
                   id="vendor_email" 
                   name="email"
                   class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                   placeholder="vendor@example.com">
            <p class="text-xs text-gray-500 mt-0.5">
                <i class="fas fa-info-circle mr-1"></i>
                Portal login will be created if email is provided
            </p>
        </div>
        
        <!-- Address -->
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">
                Address
            </label>
            <textarea id="vendor_address" 
                      name="address" 
                      rows="2"
                      class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                      placeholder="Enter complete address"></textarea>
        </div>
        
        <!-- Vendor is Active -->
        <div class="flex items-start">
            <input type="checkbox" 
                   id="vendor_is_active" 
                   name="is_active" 
                   value="1"
                   checked
                   class="mt-0.5 h-3.5 w-3.5 text-orange-600 focus:ring-orange-500 border-gray-300 rounded">
            <label for="vendor_is_active" class="ml-2">
                <span class="text-xs font-medium text-gray-900">Vendor is active</span>
                <p class="text-xs text-gray-500">Active vendors can be assigned to orders</p>
            </label>
        </div>
    </div>
    
    <!-- Fixed Footer with Buttons -->
    <div class="border-t px-4 py-3 flex justify-between bg-gray-50 rounded-b-lg flex-shrink-0">
        <button type="button" 
                onclick="closeVendorModal()"
                class=" p-8 px-4 py-1.5 text-sm border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition flex items-center gap-1.5">
           
            Cancel
        </button>
        <button type="submit" 
                class="p-8 px-4 py-1.5 text-sm bg-orange-600 hover:bg-orange-700 text-white font-medium rounded-lg transition flex items-center gap-1.5">
           
            Create Vendor
        </button>
    </div>
</form>
        
    </div>
</div>

@endsection
@push('scripts')
<script>

const amountInput = document.querySelector('input[name="amount"]');
const paidAmountInput = document.querySelector('input[name="paid_amount"]');

function updateBalance() {
    const amount = parseFloat(amountInput.value) || 0;
    const paidAmount = parseFloat(paidAmountInput.value) || 0;
    const balance = amount - paidAmount;

    document.getElementById('orderAmountDisplay').textContent = '₹' + amount.toFixed(2);
    document.getElementById('paidAmountDisplay').textContent = '₹' + paidAmount.toFixed(2);
    document.getElementById('balanceDisplay').textContent = '₹' + balance.toFixed(2);
}

amountInput.addEventListener('input', updateBalance);
paidAmountInput.addEventListener('input', updateBalance);

// Validate paid amount doesn't exceed order amount
document.querySelector('form').addEventListener('submit', function(e) {
    const amount = parseFloat(amountInput.value) || 0;
    const paidAmount = parseFloat(paidAmountInput.value) || 0;

    if (paidAmount > amount) {
        e.preventDefault();
        alert('Paid amount cannot be greater than order amount!');
        paidAmountInput.focus();
    }
});
function startSync(e, form) {
    // if (!confirm('Sync this order from WooCommerce?')) {
    //     e.preventDefault();
    //     return;
    // }

    const btn = document.getElementById('syncBtn');
    const icon = document.getElementById('syncIcon');
    const text = document.getElementById('syncText');

    btn.disabled = true;
   btn.classList.add('cursor-not-allowed', 'opacity-75');
    icon.classList.add('animate-spin');
    text.textContent = 'Syncing...';
}
// ✅ VENDOR MODAL CODE - KEEP ONLY ONE VERSION
let currentVendorType = '';

function openVendorModal(type) {
    currentVendorType = type;
    document.getElementById('vendor_type').value = type;
    document.getElementById('vendorModal').classList.remove('hidden');
    document.getElementById('vendor_name').focus();
    
    // Pre-select the correct vendor type radio button
    const radioButton = document.querySelector(`input[name="type_radio"][value="${type}"]`);
    if (radioButton) {
        radioButton.checked = true;
    }
}

function closeVendorModal() {
    document.getElementById('vendorModal').classList.add('hidden');
    document.getElementById('vendorForm').reset();
}

let isSubmitting = false; // ✅ Add global flag

document.getElementById('vendorForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    // ✅ Prevent multiple submissions
    if (isSubmitting) {
        console.log('Already submitting, please wait...');
        return;
    }
    
    isSubmitting = true; // ✅ Set flag
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
    
    try {
        const response = await fetch('{{ route("vendors.quick-store") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Add new vendor to ALL dropdowns of this type (for each product)
            const dropdowns = document.querySelectorAll(`select[name*="[${currentVendorType}_vendor_id]"]`);
            dropdowns.forEach(dropdown => {
                const option = new Option(data.vendor.name, data.vendor.id, true, true);
                dropdown.add(option);
            });
            
            // Close modal and reset form
            closeVendorModal();
            
            // Show success message
            alert('Vendor created successfully!');
        } else {
            alert('Error: ' + (data.message || 'Failed to create vendor'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error creating vendor. Please try again.');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
        isSubmitting = false; // ✅ Reset flag
    }
});
// Close modal on outside click
document.getElementById('vendorModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeVendorModal();
    }
});
</script>
<style>
/* Selected state - Gradient background */
input[type="radio"][value="dye"]:checked + div {
    background: linear-gradient(to bottom right, #3b82f6, #2563eb) !important;
    border-color: #2563eb !important;
}

input[type="radio"][value="print"]:checked + div {
    background: linear-gradient(to bottom right, #10b981, #059669) !important;
    border-color: #059669 !important;
}

input[type="radio"][value="emb"]:checked + div {
    background: linear-gradient(to bottom right, #a855f7, #9333ea) !important;
    border-color: #9333ea !important;
}

input[type="radio"][value="master"]:checked + div {
    background: linear-gradient(to bottom right, #f97316, #ea580c) !important;
    border-color: #ea580c !important;
}

input[type="radio"]:checked + div {
    transform: scale(1.03);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

input[type="radio"]:checked + div i,
input[type="radio"]:checked + div p {
    color: white !important;
}

input[type="radio"] + div {
    transition: all 0.2s ease;
}

input[type="radio"] + div:hover {
    transform: translateY(-1px);
}

input[type="radio"]:checked + div:hover {
    transform: scale(1.03);
}
</style>
@endpush

