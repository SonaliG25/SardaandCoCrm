@extends('layouts.app')

@section('title', 'Vendors')

@section('page-title', 'Vendors Management')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Vendors Management</h1>
            <p class="text-gray-600 mt-1">Manage your dye, print, embroidery, and master vendors</p>
        </div>
        <div class="mt-4 md:mt-0">
           <a href="{{ route('vendors.create') }}" class="inline-flex items-center px-4 py-2 bg-sarda-600 hover:bg-sarda-700 text-white font-medium rounded-lg shadow-lg hover:shadow-xl transition-all transform hover:scale-105">
    <i class="fas fa-plus mr-2"></i>
    Add Vendor
</a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Dye Vendors</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $vendors->where('type', 'dye')->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-tint text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Print Vendors</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $vendors->where('type', 'print')->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-print text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Emb Vendors</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $vendors->where('type', 'emb')->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-cut text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-orange-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Masters</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $vendors->where('type', 'master')->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-user-tie text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <form method="GET" action="{{ route('vendors.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Search -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                <div class="relative">
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Vendor name, contact person..." 
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                </div>
            </div>

            <!-- Type Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Vendor Type</label>
                <select name="type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">All Types</option>
                    <option value="dye" {{ request('type') == 'dye' ? 'selected' : '' }}>Dye</option>
                    <option value="print" {{ request('type') == 'print' ? 'selected' : '' }}>Print</option>
                    <option value="emb" {{ request('type') == 'emb' ? 'selected' : '' }}>Embroidery</option>
                    <option value="master" {{ request('type') == 'master' ? 'selected' : '' }}>Master</option>
                </select>
            </div>

            <!-- Filter Button -->
            <div class="flex items-end space-x-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg shadow transition">
                    <i class="fas fa-filter mr-2"></i>
                    Filter
                </button>
                <a href="{{ route('vendors.index') }}" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-lg transition">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Vendors Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vendor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact Person</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pending Orders</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($vendors as $vendor)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold mr-3
                                    {{ $vendor->type == 'dye' ? 'bg-blue-500' : ($vendor->type == 'print' ? 'bg-green-500' : ($vendor->type == 'emb' ? 'bg-purple-500' : 'bg-orange-500')) }}">
                                    <i class="fas 
                                        {{ $vendor->type == 'dye' ? 'fa-tint' : ($vendor->type == 'print' ? 'fa-print' : ($vendor->type == 'emb' ? 'fa-cut' : 'fa-user-tie')) }}"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-semibold text-gray-900">{{ $vendor->name }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-3 py-1 text-xs font-semibold rounded-full
                                {{ $vendor->type == 'dye' ? 'bg-blue-100 text-blue-800' : ($vendor->type == 'print' ? 'bg-green-100 text-green-800' : ($vendor->type == 'emb' ? 'bg-purple-100 text-purple-800' : 'bg-orange-100 text-orange-800')) }}">
                                {{ ucfirst($vendor->type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $vendor->contact_person ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">
                                @if($vendor->phone)
                                    <a href="tel:{{ $vendor->phone }}" class="text-primary-600 hover:text-primary-800">
                                        <i class="fas fa-phone mr-1"></i>{{ $vendor->phone }}
                                    </a>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">
                                @if($vendor->email)
                                    <a href="mailto:{{ $vendor->email }}" class="text-primary-600 hover:text-primary-800">
                                        {{ $vendor->email }}
                                    </a>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($vendor->pending_count > 0)
                                <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded-full">
                                    {{ $vendor->pending_count }} pending
                                </span>
                            @else
                                <span class="text-gray-400 text-sm">No pending</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <button onclick="toggleStatus({{ $vendor->id }})" 
                                    class="relative inline-flex items-center h-6 rounded-full w-11 transition-colors focus:outline-none {{ $vendor->is_active ? 'bg-green-500' : 'bg-gray-300' }}">
                                <span class="sr-only">Toggle status</span>
                                <span class="inline-block w-4 h-4 transform bg-white rounded-full transition-transform {{ $vendor->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                            </button>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-3">
                                <a href="{{ route('vendors.show', $vendor) }}" 
                                   class="text-primary-600 hover:text-primary-900" 
                                   title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('vendors.edit', $vendor) }}" 
                                   class="text-blue-600 hover:text-blue-900"
                                   title="Edit Vendor">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('vendors.destroy', $vendor) }}" 
                                      method="POST" 
                                      class="inline"
                                      onsubmit="return confirm('Are you sure you want to delete this vendor?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="text-red-600 hover:text-red-900"
                                            title="Delete Vendor">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center">
                            <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
                                <i class="fas fa-truck text-3xl text-gray-400"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No vendors found</h3>
                            <p class="text-sm text-gray-600 mb-4">Get started by adding your first vendor</p>
                            <a href="{{ route('vendors.create') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg shadow transition">
                                <i class="fas fa-plus mr-2"></i>
                                Add Vendor
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($vendors->hasPages())
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
            {{ $vendors->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleStatus(vendorId) {
    fetch(`/vendors/${vendorId}/toggle-status`, {
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
        } else {
            alert('Failed to update status');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to update status');
    });
}
</script>
@endpush