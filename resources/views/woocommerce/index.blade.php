@extends('layouts.app')

@section('title', 'WooCommerce Integration')

@section('page-title', 'WooCommerce Integration')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">WooCommerce Integration</h1>
                <p class="text-gray-600 mt-1">Sync orders automatically from your WooCommerce store</p>
            </div>
            <div>
                <i class="fas fa-shopping-cart text-5xl text-sarda-500"></i>
            </div>
        </div>
    </div>

    <!-- API Status -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="bg-gradient-to-r from-sarda-600 to-sarda-700 px-6 py-4">
            <h3 class="text-lg font-semibold text-white">
                <i class="fas fa-plug mr-2"></i>API Connection
            </h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="text-sm text-gray-600">Website URL</label>
                    <p class="font-mono text-sm text-gray-900">{{ config('services.woocommerce.url') ?: 'Not configured' }}</p>
                </div>
                <div>
                    <label class="text-sm text-gray-600">API Version</label>
                    <p class="font-mono text-sm text-gray-900">{{ config('services.woocommerce.version') }}</p>
                </div>
                <div>
                    <label class="text-sm text-gray-600">Consumer Key</label>
                    <p class="font-mono text-sm text-gray-900">
                        {{ config('services.woocommerce.consumer_key') ? str_repeat('•', 20) . substr(config('services.woocommerce.consumer_key'), -4) : 'Not configured' }}
                    </p>
                </div>
                <div>
                    <label class="text-sm text-gray-600">Consumer Secret</label>
                    <p class="font-mono text-sm text-gray-900">
                        {{ config('services.woocommerce.consumer_secret') ? str_repeat('•', 20) : 'Not configured' }}
                    </p>
                </div>
            </div>

            <form action="{{ route('woocommerce.test-connection') }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow transition">
                    <i class="fas fa-check-circle mr-2"></i>
                    Test Connection
                </button>
            </form>
        </div>
    </div>

    <!-- Sync Orders -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="bg-gradient-to-r from-green-600 to-green-700 px-6 py-4">
            <h3 class="text-lg font-semibold text-white">
                <i class="fas fa-sync mr-2"></i>Sync Orders
            </h3>
        </div>
        <div class="p-6">
            <form action="{{ route('woocommerce.sync-orders') }}" method="POST" class="space-y-4">
                @csrf
                
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Number of Orders</label>
                        <select name="limit" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sarda-500 focus:border-sarda-500">
                            <option value="10">10 orders</option>
                            <option value="20">20 orders</option>
                            <option value="50">50 orders</option>
                            <option value="100">100 orders</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Order Status</label>
                        <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sarda-500 focus:border-sarda-500">
                            <option value="any">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="on-hold">On Hold</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Page</label>
                        <input type="number" name="page" value="1" min="1" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sarda-500 focus:border-sarda-500">
                    </div>
                </div>

                <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg shadow transition">
                    <i class="fas fa-sync-alt mr-2"></i>
                    Sync Orders Now
                </button>
            </form>
        </div>
    </div>

    <!-- Webhook Setup -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="bg-gradient-to-r from-purple-600 to-purple-700 px-6 py-4">
            <h3 class="text-lg font-semibold text-white">
                <i class="fas fa-webhook mr-2"></i>Webhook Setup (Optional)
            </h3>
        </div>
        <div class="p-6">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                <p class="text-sm text-blue-900">
                    <i class="fas fa-info-circle mr-2"></i>
                    Set up webhooks in WooCommerce to automatically sync orders when they're created or updated.
                </p>
            </div>

            <div class="space-y-2">
                <div>
                    <label class="text-sm font-medium text-gray-700">Webhook URL:</label>
                    <div class="flex items-center space-x-2 mt-1">
                        <input type="text" 
                               value="{{ route('woocommerce.webhook') }}"
                               readonly
                               class="flex-1 px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 font-mono text-sm">
                        <button onclick="copyWebhookUrl()" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>

                <div class="pt-4">
                    <p class="text-sm text-gray-600 mb-2"><strong>Setup Instructions:</strong></p>
                    <ol class="text-sm text-gray-600 space-y-1 list-decimal list-inside">
                        <li>Go to WooCommerce → Settings → Advanced → Webhooks</li>
                        <li>Click "Add webhook"</li>
                        <li>Name: "CRM Sync"</li>
                        <li>Status: Active</li>
                        <li>Topic: Order created / Order updated</li>
                        <li>Delivery URL: (Copy the webhook URL above)</li>
                        <li>Secret: {{ config('services.woocommerce.webhook_secret') }}</li>
                        <li>Save webhook</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function copyWebhookUrl() {
    const input = document.querySelector('input[readonly]');
    input.select();
    document.execCommand('copy');
    alert('Webhook URL copied to clipboard!');
}
</script>
@endpush