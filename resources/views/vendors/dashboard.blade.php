@extends('layouts.vendor')

@section('title', 'Vendor Dashboard')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">
            Welcome, {{ $vendor->name }}! 
        </h1>
        <p class="text-gray-600 mt-1">Manage your workflow and update order statuses</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="rounded-lg shadow-lg p-6" style="background: linear-gradient(to bottom right, #3b82f6, #2563eb);">
            <div style="color: white;">
                <p class="text-sm mb-1" style="opacity: 0.9;">Total Orders</p>
                <p class="text-3xl font-bold">{{ $stats['total_orders'] }}</p>
            </div>
        </div>

        <div class="rounded-lg shadow-lg p-6" style="background: linear-gradient(to bottom right, #ef4444, #dc2626);">
            <div style="color: white;">
                <p class="text-sm mb-1" style="opacity: 0.9;">Pending</p>
                <p class="text-3xl font-bold">{{ $stats['pending'] }}</p>
            </div>
        </div>

        <div class="rounded-lg shadow-lg p-6" style="background: linear-gradient(to bottom right, #f59e0b, #d97706);">
            <div style="color: white;">
                <p class="text-sm mb-1" style="opacity: 0.9;">In Progress</p>
                <p class="text-3xl font-bold">{{ $stats['in_progress'] }}</p>
            </div>
        </div>

        <div class="rounded-lg shadow-lg p-6" style="background: linear-gradient(to bottom right, #10b981, #059669);">
            <div style="color: white;">
                <p class="text-sm mb-1" style="opacity: 0.9;">Completed</p>
                <p class="text-3xl font-bold">{{ $stats['completed'] }}</p>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Recent Orders</h3>
            <a href="{{ route('vendor.orders') }}" class="text-sm" style="color: #f2601f;">
                View All →
            </a>
        </div>

        @if($orders->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">My Stage</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($orders->take(10) as $order)
                    @php
                        $myStage = null;
                        $myStatus = null;
                        
                        if ($order->dye_vendor_id == $vendor->id) {
                            $myStage = 'Dye';
                            $myStatus = $order->dye_status;
                        } elseif ($order->print_vendor_id == $vendor->id) {
                            $myStage = 'Print';
                            $myStatus = $order->print_status;
                        } elseif ($order->emb_vendor_id == $vendor->id) {
                            $myStage = 'Embroidery';
                            $myStatus = $order->emb_status;
                        }
                    @endphp
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">#{{ $order->order_id }}</div>
                            <div class="text-sm text-gray-500">{{ $order->order_date->format('d M Y') }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">{{ \Illuminate\Support\Str::limit($order->product_description, 40) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $myStage }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-3 py-1 text-xs font-semibold rounded-full
                                @if($myStatus == 'completed') bg-green-100 text-green-800
                                @elseif($myStatus == 'in_progress') bg-yellow-100 text-yellow-800
                                @else bg-red-100 text-red-800
                                @endif">
                                {{ ucfirst(str_replace('_', ' ', $myStatus)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <button onclick="openUpdateModal({{ $order->id }}, '{{ strtolower($myStage) }}', '{{ $myStatus }}')"
                                    class="text-white px-3 py-1 rounded"
                                    style="background: #f2601f;">
                                Update
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-12">
            <i class="fas fa-inbox text-gray-400 text-5xl mb-3"></i>
            <p class="text-gray-600">No orders assigned yet</p>
        </div>
        @endif
    </div>
</div>

<!-- Update Status Modal -->
<div id="updateModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Update Status</h3>
        
        <form id="updateForm" method="POST">
            @csrf
            <input type="hidden" name="stage" id="modal_stage">
            <input type="hidden" name="old_status" id="modal_old_status">
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status" 
                        id="modal_status"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="pending">Pending</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                </select>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                <textarea name="notes" 
                          rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                          placeholder="Add any notes..."></textarea>
            </div>
            
            <div class="flex space-x-3">
                <button type="submit" 
                        class="flex-1 px-4 py-2 text-white rounded-lg"
                        style="background: #f2601f;">
                    Update
                </button>
                <button type="button" 
                        onclick="closeUpdateModal()"
                        class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-lg">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openUpdateModal(orderId, stage, currentStatus) {
    document.getElementById('updateForm').action = `/vendor/orders/${orderId}/update-status`;
    document.getElementById('modal_stage').value = stage;
    document.getElementById('modal_old_status').value = currentStatus;
    document.getElementById('modal_status').value = currentStatus;
    
    document.getElementById('updateModal').classList.remove('hidden');
    document.getElementById('updateModal').classList.add('flex');
}

function closeUpdateModal() {
    document.getElementById('updateModal').classList.add('hidden');
    document.getElementById('updateModal').classList.remove('flex');
}
</script>
@endsection