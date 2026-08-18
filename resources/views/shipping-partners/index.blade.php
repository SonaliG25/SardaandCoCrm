@extends('layouts.app')

@section('title', 'Shipping Partners')

@section('page-title', 'Shipping Partners Management')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Shipping Partners Management</h1>
            <p class="text-gray-600 mt-1">Manage your courier and logistics partners</p>
        </div>
        <div class="mt-4 md:mt-0">
            <a href="{{ route('shipping-partners.create') }}" class="inline-flex items-center px-4 py-2 bg-sarda-600 hover:bg-sarda-700 text-white font-medium rounded-lg shadow-lg hover:shadow-xl transition-all transform hover:scale-105">
                <i class="fas fa-plus mr-2"></i>
                Add Partner
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-sarda-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Total Partners</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $partners->total() }}</p>
                </div>
                <div class="w-12 h-12 bg-sarda-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-shipping-fast text-sarda-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Total Shipments</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $partners->sum('total_shipments') }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-box text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">In Transit</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $partners->sum('in_transit') }}</p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-truck text-yellow-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Delivered</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $partners->sum('delivered') }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <form method="GET" action="{{ route('shipping-partners.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Search -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                <div class="relative">
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Partner name, contact person..." 
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sarda-500 focus:border-sarda-500">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                </div>
            </div>

            <!-- Filter Button -->
            <div class="flex items-end space-x-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-sarda-600 hover:bg-sarda-700 text-white font-medium rounded-lg shadow transition">
                    <i class="fas fa-filter mr-2"></i>
                    Filter
                </button>
                <a href="{{ route('shipping-partners.index') }}" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-lg transition">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Partners Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Partner</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact Person</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Shipments</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">In Transit</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($partners as $partner)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-full bg-sarda-100 flex items-center justify-center text-sarda-700 font-bold mr-3">
                                    <i class="fas fa-shipping-fast"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-semibold text-gray-900">{{ $partner->name }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $partner->contact_person ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">
                                @if($partner->phone)
                                    <a href="tel:{{ $partner->phone }}" class="text-sarda-600 hover:text-sarda-800">
                                        <i class="fas fa-phone mr-1"></i>{{ $partner->phone }}
                                    </a>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">
                                @if($partner->email)
                                    <a href="mailto:{{ $partner->email }}" class="text-sarda-600 hover:text-sarda-800">
                                        {{ $partner->email }}
                                    </a>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm font-semibold rounded-full">
                                {{ $partner->total_shipments }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($partner->in_transit > 0)
                                <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-sm font-semibold rounded-full">
                                    {{ $partner->in_transit }}
                                </span>
                            @else
                                <span class="text-gray-400 text-sm">0</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <button onclick="toggleStatus({{ $partner->id }})" 
                                    class="relative inline-flex items-center h-6 rounded-full w-11 transition-colors focus:outline-none {{ $partner->is_active ? 'bg-green-500' : 'bg-gray-300' }}">
                                <span class="sr-only">Toggle status</span>
                                <span class="inline-block w-4 h-4 transform bg-white rounded-full transition-transform {{ $partner->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                            </button>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-3">
                                <a href="{{ route('shipping-partners.show', $partner) }}" 
                                   class="text-sarda-600 hover:text-sarda-900" 
                                   title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('shipping-partners.edit', $partner) }}" 
                                   class="text-blue-600 hover:text-blue-900"
                                   title="Edit Partner">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('shipping-partners.destroy', $partner) }}" 
                                      method="POST" 
                                      class="inline"
                                      onsubmit="return confirm('Are you sure you want to delete this shipping partner?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="text-red-600 hover:text-red-900"
                                            title="Delete Partner">
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
                                <i class="fas fa-shipping-fast text-3xl text-gray-400"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No shipping partners found</h3>
                            <p class="text-sm text-gray-600 mb-4">Get started by adding your first shipping partner</p>
                            <a href="{{ route('shipping-partners.create') }}" class="inline-flex items-center px-4 py-2 bg-sarda-600 hover:bg-sarda-700 text-white font-medium rounded-lg shadow transition">
                                <i class="fas fa-plus mr-2"></i>
                                Add Partner
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($partners->hasPages())
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
            {{ $partners->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleStatus(partnerId) {
    fetch(`/shipping-partners/${partnerId}/toggle-status`, {
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