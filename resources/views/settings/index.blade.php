@extends('layouts.app')

@section('title', 'Settings')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">
            <i class="fas fa-cog mr-2" style="color: #f2601f;"></i>
            Settings
        </h1>
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

    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Sidebar Navigation -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-sm p-4">
                <nav class="space-y-1">
                    <a href="#profile" 
                       onclick="showTab('profile'); return false;"
                       id="tab-profile"
                       class="settings-tab active flex items-center px-4 py-3 text-sm font-medium rounded-lg">
                        <i class="fas fa-user mr-3"></i>
                        Profile Settings
                    </a>
                    <a href="#password" 
                       onclick="showTab('password'); return false;"
                       id="tab-password"
                       class="settings-tab flex items-center px-4 py-3 text-sm font-medium rounded-lg">
                        <i class="fas fa-lock mr-3"></i>
                        Change Password
                    </a>
                    <a href="#business" 
                       onclick="showTab('business'); return false;"
                       id="tab-business"
                       class="settings-tab flex items-center px-4 py-3 text-sm font-medium rounded-lg">
                        <i class="fas fa-building mr-3"></i>
                        Business Details
                    </a>
                    <a href="#api" 
                       onclick="showTab('api'); return false;"
                       id="tab-api"
                       class="settings-tab flex items-center px-4 py-3 text-sm font-medium rounded-lg">
                        <i class="fas fa-plug mr-3"></i>
                        API Settings
                    </a>
                </nav>
            </div>
        </div>

        <!-- Content Area -->
        <div class="lg:col-span-2">
            
            <!-- Profile Settings -->
            <div id="content-profile" class="settings-content">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Profile Information</h3>
                    
                    <form method="POST" action="{{ route('settings.update-profile') }}">
                        @csrf
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                                <input type="text" 
                                       name="name" 
                                       value="{{ old('name', $user->name) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-opacity-50"
                                       required>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                                <input type="email" 
                                       name="email" 
                                       value="{{ old('email', $user->email) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                                       required>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                                <input type="text" 
                                       name="phone" 
                                       value="{{ old('phone', $user->phone) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            </div>
                        </div>
                        
                        <div class="mt-6">
                            <button type="submit" 
                                    class="px-6 py-2 text-white font-medium rounded-lg"
                                    style="background: #f2601f;">
                                <i class="fas fa-save mr-2"></i>
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Change Password -->
            <div id="content-password" class="settings-content hidden">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Change Password</h3>
                    
                    <form method="POST" action="{{ route('settings.update-password') }}">
                        @csrf
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                                <input type="password" 
                                       name="current_password" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                                       required>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                                <input type="password" 
                                       name="new_password" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                                       required>
                                <p class="text-xs text-gray-500 mt-1">Minimum 8 characters</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                                <input type="password" 
                                       name="new_password_confirmation" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                                       required>
                            </div>
                        </div>
                        
                        <div class="mt-6">
                            <button type="submit" 
                                    class="px-6 py-2 text-white font-medium rounded-lg"
                                    style="background: #f2601f;">
                                <i class="fas fa-key mr-2"></i>
                                Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Business Details -->
            <div id="content-business" class="settings-content hidden">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Business Information</h3>
                    
                    <form method="POST" action="{{ route('settings.update-business') }}">
                        @csrf
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Business Name</label>
                                <input type="text" 
                                       name="business_name" 
                                       value="{{ old('business_name', $settings['business']['name']) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                                       required>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Business Address</label>
                                <textarea name="business_address" 
                                          rows="3"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg">{{ old('business_address', $settings['business']['address']) }}</textarea>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Business Phone</label>
                                    <input type="text" 
                                           name="business_phone" 
                                           value="{{ old('business_phone', $settings['business']['phone']) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Business Email</label>
                                    <input type="email" 
                                           name="business_email" 
                                           value="{{ old('business_email', $settings['business']['email']) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">GST Number</label>
                                <input type="text" 
                                       name="business_gst" 
                                       value="{{ old('business_gst', $settings['business']['gst']) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                                       placeholder="27AABCU9603R1ZX">
                            </div>
                        </div>
                        
                        <div class="mt-6">
                            <button type="submit" 
                                    class="px-6 py-2 text-white font-medium rounded-lg"
                                    style="background: #f2601f;">
                                <i class="fas fa-save mr-2"></i>
                                Save Business Details
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- API Settings -->
            <div id="content-api" class="settings-content hidden">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">API Configurations</h3>
                    
                    <form method="POST" action="{{ route('settings.update-api') }}">
                        @csrf
                        
                        <!-- WooCommerce -->
                        <div class="mb-6">
                            <h4 class="text-md font-semibold text-gray-800 mb-3 flex items-center">
                                <i class="fab fa-wordpress text-blue-600 mr-2"></i>
                                WooCommerce API
                            </h4>
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Store URL</label>
                                    <input type="url" 
                                           name="woocommerce_url" 
                                           value="{{ old('woocommerce_url', $settings['woocommerce']['url']) }}"
                                           placeholder="https://yourdomain.com"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Consumer Key</label>
                                    <input type="text" 
                                           name="woocommerce_consumer_key" 
                                           value="{{ old('woocommerce_consumer_key', $settings['woocommerce']['consumer_key'] ? '***' . substr($settings['woocommerce']['consumer_key'], -4) : '') }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                                           placeholder="ck_xxxxxxxxxxxxxxxx">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Consumer Secret</label>
                                    <input type="password" 
                                           name="woocommerce_consumer_secret" 
                                           value="{{ old('woocommerce_consumer_secret') }}"
                                           placeholder="Leave blank to keep current"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                </div>
                            </div>
                            
                            <!-- Test Connection Button -->
                            <button type="button" 
                                    onclick="testConnection('woocommerce')"
                                    class="mt-3 px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-sm">
                                <i class="fas fa-plug mr-1"></i>
                                Test Connection
                            </button>
                            <span id="woocommerce-status" class="ml-3 text-sm"></span>
                        </div>

                        <!-- Razorpay -->
                        <div class="mb-6 pt-6 border-t">
                            <h4 class="text-md font-semibold text-gray-800 mb-3 flex items-center">
                                <i class="fas fa-credit-card text-purple-600 mr-2"></i>
                                Razorpay API
                            </h4>
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Key ID</label>
                                    <input type="text" 
                                           name="razorpay_key_id" 
                                           value="{{ old('razorpay_key_id', $settings['razorpay']['key_id'] ? '***' . substr($settings['razorpay']['key_id'], -4) : '') }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                                           placeholder="rzp_live_xxxxxxxx">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Key Secret</label>
                                    <input type="password" 
                                           name="razorpay_key_secret" 
                                           value="{{ old('razorpay_key_secret') }}"
                                           placeholder="Leave blank to keep current"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                </div>
                            </div>
                            
                            <!-- Test Connection Button -->
                            <button type="button" 
                                    onclick="testConnection('razorpay')"
                                    class="mt-3 px-4 py-2 bg-purple-500 hover:bg-purple-600 text-white rounded-lg text-sm">
                                <i class="fas fa-plug mr-1"></i>
                                Test Connection
                            </button>
                            <span id="razorpay-status" class="ml-3 text-sm"></span>
                        </div>

                        <!-- Delhivery -->
                        <div class="mb-6 pt-6 border-t">
                            <h4 class="text-md font-semibold text-gray-800 mb-3 flex items-center">
                                <i class="fas fa-truck text-green-600 mr-2"></i>
                                Delhivery API
                            </h4>
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">API Token</label>
                                    <input type="password" 
                                           name="delhivery_api_token" 
                                           value="{{ old('delhivery_api_token') }}"
                                           placeholder="Leave blank to keep current"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                    <p class="text-xs text-gray-500 mt-1">Get your token from Delhivery dashboard</p>
                                </div>
                            </div>
                            
                            <!-- Test Connection Button -->
                            <button type="button" 
                                    onclick="testConnection('delhivery')"
                                    class="mt-3 px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg text-sm">
                                <i class="fas fa-plug mr-1"></i>
                                Test Connection
                            </button>
                            <span id="delhivery-status" class="ml-3 text-sm"></span>
                        </div>
                        
                        <div class="mt-6 pt-6 border-t">
                            <button type="submit" 
                                    class="px-6 py-2 text-white font-medium rounded-lg"
                                    style="background: #f2601f;">
                                <i class="fas fa-save mr-2"></i>
                                Save API Settings
                            </button>
                            <p class="text-xs text-gray-500 mt-2">
                                <i class="fas fa-info-circle mr-1"></i>
                                Some changes may require clearing cache or restarting services
                            </p>
                        </div>
                    </form>
                </div>
            </div>
<div class="border border-gray-200 rounded-lg p-4 mt-4">
    <h4 class="text-sm font-medium text-gray-900">Sync Workflow Statuses</h4>
    <p class="text-xs text-gray-500 mt-1 mb-4">
        Rolls up each order's per-product Dye/Print/Emb/Master progress onto the order itself,
        fixing incorrect 0% (or stale) Workflow percentages shown on the Dashboard and Orders list
        for orders that haven't been re-saved since this sync was added.
    </p>

    <form action="{{ route('orders.sync-workflow-statuses') }}" method="POST"
          onsubmit="return confirm(this.order_number.value ? 'Sync workflow status for order ' + this.order_number.value + '?' : 'This will re-sync workflow status for ALL orders with products. Continue?')">
        @csrf
        <div class="flex flex-col sm:flex-row sm:items-end gap-3">
            <div class="flex-1">
                <label class="block text-xs font-medium text-gray-700 mb-1">
                    Order Number (optional)
                </label>
                <input type="text" name="order_number"
                       placeholder="e.g. 8874 - leave blank to check all orders"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-sarda-500 focus:border-sarda-500">
            </div>
            <button type="submit"
                    class="px-4 py-2 text-white text-sm font-medium rounded-lg whitespace-nowrap"
                    style="background: #f2601f;">
                <i class="fas fa-sync mr-1"></i>
                Run Sync
            </button>
        </div>
    </form>
</div>
        </div>
    </div>
</div>

<style>
.settings-tab {
    color: #6b7280;
    transition: all 0.3s;
    cursor: pointer;
}

.settings-tab:hover {
    background: #f9fafb;
    color: #f2601f;
}

.settings-tab.active {
    background: #f2601f;
    color: white;
}
</style>

<script>
function showTab(tabName) {
    // Hide all content
    document.querySelectorAll('.settings-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active class from all tabs
    document.querySelectorAll('.settings-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Show selected content
    document.getElementById('content-' + tabName).classList.remove('hidden');
    
    // Add active class to selected tab
    document.getElementById('tab-' + tabName).classList.add('active');
}

function testConnection(type) {
    const statusSpan = document.getElementById(type + '-status');
    statusSpan.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testing...';
    
    fetch('{{ route("settings.test-connection") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ type: type })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            statusSpan.innerHTML = '<span style="color: #10b981;"><i class="fas fa-check-circle mr-1"></i>' + data.message + '</span>';
        } else {
            statusSpan.innerHTML = '<span style="color: #ef4444;"><i class="fas fa-times-circle mr-1"></i>' + data.message + '</span>';
        }
        
        // Clear status after 5 seconds
        setTimeout(() => {
            statusSpan.innerHTML = '';
        }, 5000);
    })
    .catch(error => {
        statusSpan.innerHTML = '<span style="color: #ef4444;"><i class="fas fa-times-circle mr-1"></i>Connection failed</span>';
        
        setTimeout(() => {
            statusSpan.innerHTML = '';
        }, 5000);
    });
}
</script>
@endsection