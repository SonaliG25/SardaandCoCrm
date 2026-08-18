@extends('layouts.app')

@section('title', 'Shipping Partner Details - ' . $shippingPartner->name)

@section('page-title', 'Shipping Partner Details')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('shipping-partners.index') }}" class="text-gray-600 hover:text-gray-900">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-2xl font-bold text-gray-900">{{ $shippingPartner->name }}</h1>
                <span class="px-3 py-1 text-sm font-semibold rounded-full {{ $shippingPartner->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                    {{ $shippingPartner->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
        </div>
        <div class="mt-4 md:mt-0 flex items-center space-x-3">
            <a href="{{ route('shipping-partners.edit', $shippingPartner) }}" class="inline-flex items-center px-4 py-2 bg-sarda-600 hover:bg-sarda-700 text-white font-medium rounded-lg shadow-lg transition">
                <i class="fas fa-edit mr-2"></i>
                Edit Partner
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
            <div class="text-sm text-gray-600 mb-1">Total Shipments</div>
            <div class="text-2xl font-bold text-gray-900">{{ $stats['total_shipments'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-yellow-500">
            <div class="text-sm text-gray-600 mb-1">In Transit</div>
            <div class="text-2xl font-bold text-gray-900">{{ $stats['in_transit'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
            <div class="text-sm text-gray-600 mb-1">Delivered</div>
            <div class="text-2xl font-bold text-gray-900">{{ $stats['delivered'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-red-500">
            <div class="text-sm text-gray-600 mb-1">Failed</div>
            <div class="text-2xl font-bold text-gray-900">{{ $stats['failed'] }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Partner Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Contact Information -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="bg-gradient-to-r from-sarda-600 to-sarda-700 px-6 py-4">
                    <h3 class="text-lg font-semibold text-white">
                        <i class="fas fa-info-circle mr-2"></i>Contact Information
                    </h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm text-gray-600">Partner Name</label>
                            <p class="font-semibold text-gray-900">{{ $shippingPartner->name }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">Contact Person</label>
                            <p class="font-semibold text-gray-900">{{ $shippingPartner->contact_person ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">Phone</label>
                            <p class="font-semibold text-gray-900">
                                @if($shippingPartner->phone)
                                    <a href="tel:{{ $shippingPartner->phone }}" class="text-sarda-600 hover:text-sarda-800">
                                        {{ $shippingPartner->phone }}
                                    </a>
                                @else
                                    N/A
                                @endif
                            </p>
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">Email</label>
                            <p class="font-semibold text-gray-900">
                                @if($shippingPartner->email)
                                    <a href="mailto:{{ $shippingPartner->email }}" class="text-sarda-600 hover:text-sarda-800">
                                        {{ $shippingPartner->email }}
                                    </a>
                                @else
                                    N/A
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- API Configuration -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
                    <h3 class="text-lg font-semibold text-white">
                        <i class="fas fa-code mr-2"></i>API Configuration
                    </h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="text-sm text-gray-600">API Key</label>
                            <p class="font-mono text-sm text-gray-900">
                                {{ $shippingPartner->api_key ? str_repeat('•', 20) . substr($shippingPartner->api_key, -4) : 'Not configured' }}
                            </p>
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">API Secret</label>
                            <p class="font-mono text-sm text-gray-900">
                                {{ $shippingPartner->api_secret ? str_repeat('•', 20) : 'Not configured' }}
                            </p>
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">Tracking URL</label>
                            <p class="font-mono text-sm text-gray-900 break-all">
                                {{ $shippingPartner->tracking_url ?? 'Not configured' }}
                            </p>
                        </div>
                    </div>

                    @if($shippingPartner->api_key && $shippingPartner->tracking_url)
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 flex items-start">
                        <i class="fas fa-check-circle text-green-600 mt-1 mr-3"></i>
                        <div>
                            <p class="text-sm font-semibold text-green-900">API Integration Active</p>
                            <p class="text-xs text-green-700 mt-1">Automatic tracking updates are enabled</p>
                        </div>
                    </div>
                    @else
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 flex items-start">
                        <i class="fas fa-exclamation-triangle text-yellow-600 mt-1 mr-3"></i>
                        <div>
                            <p class="text-sm font-semibold text-yellow-900">API Not Configured</p>
                            <p class="text-xs text-yellow-700 mt-1">Manual tracking updates only</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Shipments -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="bg-gradient-to-r from-sarda-600 to-sarda-700 px-6 py-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-white">
                        <i class="fas fa-box mr-2"></i>Recent Shipments
                    </h3>
                    <span class="text-white text-sm">Total: {{ $orders->total() }}</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">AWB</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($orders as $order)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-bold text-gray-900">{{ $order->order_id }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-900">{{ $order->customer->name }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-mono text-gray-900">{{ $order->awb_number ?? '-' }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full
                                        @if($order->shipping_status == 'delivered') bg-green-100 text-green-800
                                        @elseif($order->shipping_status == 'in_transit') bg-blue-100 text-blue-800
                                        @elseif($order->shipping_status == 'failed') bg-red-100 text-red-800
                                        @else bg-yellow-100 text-yellow-800
                                        @endif">
                                        {{ ucfirst(str_replace('_', ' ', $order->shipping_status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="{{ route('orders.show', $order) }}" class="text-sarda-600 hover:text-sarda-900">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                    No shipments yet
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($orders->hasPages())
                <div class="bg-gray-50 px-6 py-4 border-t">
                    {{ $orders->links() }}
                </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="bg-gradient-to-r from-sarda-600 to-sarda-700 px-6 py-4">
                    <h3 class="text-lg font-semibold text-white">
                        <i class="fas fa-bolt mr-2"></i>Quick Actions
                    </h3>
                </div>
                <div class="p-6 space-y-2">
                    <a href="{{ route('shipping-partners.edit', $shippingPartner) }}" 
                       class="w-full px-4 py-2 bg-sarda-600 hover:bg-sarda-700 text-white font-medium rounded-lg transition text-center block">
                        <i class="fas fa-edit mr-2"></i>
                        Edit Partner
                    </a>

                    <button onclick="toggleStatus()" 
                            class="w-full px-4 py-2 {{ $shippingPartner->is_active ? 'bg-gray-600 hover:bg-gray-700' : 'bg-green-600 hover:bg-green-700' }} text-white font-medium rounded-lg transition text-left">
                        <i class="fas {{ $shippingPartner->is_active ? 'fa-pause' : 'fa-play' }} mr-2"></i>
                        {{ $shippingPartner->is_active ? 'Deactivate' : 'Activate' }} Partner
                    </button>

                    <form action="{{ route('shipping-partners.destroy', $shippingPartner) }}" 
                          method="POST" 
                          onsubmit="return confirm('Are you sure you want to delete this shipping partner?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition text-left">
                            <i class="fas fa-trash mr-2"></i>
                            Delete Partner
                        </button>
                    </form>
                </div>
            </div>

            <!-- Shipment Statistics -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="bg-gradient-to-r from-green-600 to-green-700 px-6 py-4">
                    <h3 class="text-lg font-semibold text-white">
                        <i class="fas fa-chart-pie mr-2"></i>Shipment Breakdown
                    </h3>
                </div>
                <div class="p-6 space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Pending</span>
                        <span class="font-semibold text-gray-900">{{ $stats['pending'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Dispatched</span>
                        <span class="font-semibold text-gray-900">{{ $stats['dispatched'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">In Transit</span>
                        <span class="font-semibold text-gray-900">{{ $stats['in_transit'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Out for Delivery</span>
                        <span class="font-semibold text-gray-900">{{ $stats['out_for_delivery'] }}</span>
                    </div>
                    <div class="flex justify-between items-center pt-3 border-t">
                        <span class="text-sm text-gray-600">Delivered</span>
                        <span class="font-semibold text-green-600">{{ $stats['delivered'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Failed</span>
                        <span class="font-semibold text-red-600">{{ $stats['failed'] }}</span>
                    </div>

                    <!-- Success Rate -->
                    <div class="pt-4 border-t">
                        <div class="flex justify-between text-sm mb-2">
                            <span class="text-gray-600">Success Rate</span>
                            <span class="font-semibold text-gray-900">
                                {{ $stats['total_shipments'] > 0 ? number_format(($stats['delivered'] / $stats['total_shipments']) * 100, 1) : 0 }}%
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-green-500 h-2 rounded-full" 
                                 style="width: {{ $stats['total_shipments'] > 0 ? ($stats['delivered'] / $stats['total_shipments']) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleStatus() {
    if (confirm('Are you sure you want to change the partner status?')) {
        fetch('{{ route("shipping-partners.toggle-status", $shippingPartner) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
}
</script>
@endpush