@extends('layouts.app')

@section('title', 'Vendor Details - ' . $vendor->name)

@section('page-title', 'Vendor Details')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('vendors.index') }}" class="text-gray-600 hover:text-gray-900">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-2xl font-bold text-gray-900">{{ $vendor->name }}</h1>
                <span class="px-3 py-1 text-sm font-semibold rounded-full
                    {{ $vendor->type == 'dye' ? 'bg-blue-100 text-blue-800' : ($vendor->type == 'print' ? 'bg-green-100 text-green-800' : ($vendor->type == 'emb' ? 'bg-purple-100 text-purple-800' : 'bg-orange-100 text-orange-800')) }}">
                    {{ $vendor->type_label }}
                </span>
                <span class="px-3 py-1 text-sm font-semibold rounded-full {{ $vendor->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                    {{ $vendor->status_text }}
                </span>
            </div>
        </div>
        <div class="mt-4 md:mt-0 flex items-center space-x-3">
            <a href="{{ route('vendors.edit', $vendor) }}" class="inline-flex items-center px-4 py-2 bg-sarda-600 hover:bg-sarda-700 text-white font-medium rounded-lg shadow-lg transition">
                <i class="fas fa-edit mr-2"></i>
                Edit Vendor
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-yellow-500">
            <div class="text-sm text-gray-600 mb-1">Pending Orders</div>
            <div class="text-2xl font-bold text-gray-900">{{ $stats['pending'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
            <div class="text-sm text-gray-600 mb-1">Received Orders</div>
            <div class="text-2xl font-bold text-gray-900">{{ $stats['received'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
            <div class="text-sm text-gray-600 mb-1">Completed Orders</div>
            <div class="text-2xl font-bold text-gray-900">{{ $stats['completed'] }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Vendor Info -->
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
                            <label class="text-sm text-gray-600">Vendor Name</label>
                            <p class="font-semibold text-gray-900">{{ $vendor->name }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">Vendor Type</label>
                            <p class="font-semibold text-gray-900">{{ $vendor->type_label }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">Contact Person</label>
                            <p class="font-semibold text-gray-900">{{ $vendor->contact_person ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">Phone</label>
                            <p class="font-semibold text-gray-900">
                                @if($vendor->phone)
                                    <a href="tel:{{ $vendor->phone }}" class="text-sarda-600 hover:text-sarda-800">
                                        {{ $vendor->phone }}
                                    </a>
                                @else
                                    N/A
                                @endif
                            </p>
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">Email</label>
                            <p class="font-semibold text-gray-900">
                                @if($vendor->email)
                                    <a href="mailto:{{ $vendor->email }}" class="text-sarda-600 hover:text-sarda-800">
                                        {{ $vendor->email }}
                                    </a>
                                @else
                                    N/A
                                @endif
                            </p>
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">Status</label>
                            <p class="font-semibold text-gray-900">
                                <span class="px-3 py-1 text-xs rounded-full {{ $vendor->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $vendor->status_text }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assigned Orders -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="bg-gradient-to-r from-sarda-600 to-sarda-700 px-6 py-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-white">
                        <i class="fas fa-list mr-2"></i>Assigned Orders
                    </h3>
                    <span class="text-white text-sm">Total: {{ $orders->total() }}</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
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
                                    <span class="text-sm text-gray-500">{{ $order->order_date->format('d M Y') }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full
                                        @if($order->$statusField == 'completed') bg-green-100 text-green-800
                                        @elseif($order->$statusField == 'received') bg-blue-100 text-blue-800
                                        @else bg-yellow-100 text-yellow-800
                                        @endif">
                                        {{ ucfirst($order->$statusField) }}
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
                                    No orders assigned yet
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
                    <a href="{{ route('vendors.edit', $vendor) }}" 
                       class="w-full px-4 py-2 bg-sarda-600 hover:bg-sarda-700 text-white font-medium rounded-lg transition text-center block">
                        <i class="fas fa-edit mr-2"></i>
                        Edit Vendor
                    </a>

                    <button onclick="toggleStatus()" 
                            class="w-full px-4 py-2 {{ $vendor->is_active ? 'bg-gray-600 hover:bg-gray-700' : 'bg-green-600 hover:bg-green-700' }} text-white font-medium rounded-lg transition text-left">
                        <i class="fas {{ $vendor->is_active ? 'fa-pause' : 'fa-play' }} mr-2"></i>
                        {{ $vendor->is_active ? 'Deactivate' : 'Activate' }} Vendor
                    </button>

                    <form action="{{ route('vendors.destroy', $vendor) }}" 
                          method="POST" 
                          onsubmit="return confirm('Are you sure you want to delete this vendor?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition text-left">
                            <i class="fas fa-trash mr-2"></i>
                            Delete Vendor
                        </button>
                    </form>
                </div>
            </div>

            <!-- Statistics -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="bg-gradient-to-r from-green-600 to-green-700 px-6 py-4">
                    <h3 class="text-lg font-semibold text-white">
                        <i class="fas fa-chart-bar mr-2"></i>Performance
                    </h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600">Completion Rate</span>
                            <span class="font-semibold text-gray-900">
                                {{ $stats['total_orders'] > 0 ? number_format(($stats['completed'] / $stats['total_orders']) * 100, 1) : 0 }}%
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-green-500 h-2 rounded-full" 
                                 style="width: {{ $stats['total_orders'] > 0 ? ($stats['completed'] / $stats['total_orders']) * 100 : 0 }}%"></div>
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
    if (confirm('Are you sure you want to change the vendor status?')) {
        fetch('{{ route("vendors.toggle-status", $vendor) }}', {
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