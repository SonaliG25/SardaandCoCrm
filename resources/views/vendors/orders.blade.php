@extends('layouts.vendor')

@section('title', 'My Orders')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">My Orders</h1>
        
        <!-- Filter -->
        <form method="GET" class="flex items-center space-x-2">
            <select name="status" 
                    onchange="this.form.submit()"
                    class="px-4 py-2 border border-gray-300 rounded-lg">
                <option value="">All Status</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
            </select>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        @if($orders->count() > 0)
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">My Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($orders as $order)
                @php
                    $statusField = $vendor->type . '_status';
                    $myStatus = $order->$statusField;
                @endphp
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">#{{ $order->order_id }}</div>
                        <div class="text-sm text-gray-500">{{ $order->order_date->format('d M Y') }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ $order->customer->name }}</div>
                        <div class="text-sm text-gray-500">{{ $order->customer->phone }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-900">{{ \Illuminate\Support\Str::limit($order->product_description, 50) }}</div>
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
                        <button onclick="openUpdateModal({{ $order->id }}, '{{ $myStatus }}')"
                                class="text-white px-3 py-1 rounded hover:opacity-90"
                                style="background: #3b82f6;">
                            <i class="fas fa-edit mr-1"></i>
                            Update
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <!-- Pagination -->
        <div class="px-6 py-4 border-t">
            {{ $orders->links() }}
        </div>
        @else
        <div class="text-center py-12">
            <i class="fas fa-inbox text-gray-400 text-5xl mb-3"></i>
            <p class="text-gray-600">No orders found</p>
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
                        style="background: #3b82f6;">
                    <i class="fas fa-save mr-1"></i>
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
function openUpdateModal(orderId, currentStatus) {
    document.getElementById('updateForm').action = `/vendor/orders/${orderId}/update-status`;
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