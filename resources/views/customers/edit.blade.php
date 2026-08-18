@extends('layouts.app')

@section('title', 'Edit Customer')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center space-x-3">
            <a href="{{ route('customers.show', $customer) }}" 
               class="text-gray-600 hover:text-gray-900">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Edit Customer</h1>
                <p class="text-gray-600 mt-1">Update customer information</p>
            </div>
        </div>
    </div>

    <!-- Error Messages -->
    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-sm p-6 max-w-2xl">
        <form method="POST" action="{{ route('customers.update', $customer) }}">
            @csrf
            @method('PUT')

            <!-- WooCommerce Info (if applicable) -->
            @if($customer->woocommerce_customer_id)
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                    <div class="flex items-center">
                        <i class="fab fa-wordpress text-blue-600 mr-2"></i>
                        <div>
                            <p class="text-sm font-medium text-blue-800">
                                WooCommerce Customer
                            </p>
                            <p class="text-xs text-blue-700">
                                Customer ID: #{{ $customer->woocommerce_customer_id }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Name -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Full Name <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="name" 
                       value="{{ old('name', $customer->name) }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                       required>
            </div>

            <!-- Email -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                <input type="email" 
                       name="email" 
                       value="{{ old('email', $customer->email) }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>

            <!-- Phone -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Phone Number <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="phone" 
                       value="{{ old('phone', $customer->phone) }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                       required>
            </div>

            <!-- Address -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                <textarea name="address" 
                          rows="3"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg">{{ old('address', $customer->address) }}</textarea>
            </div>

            <!-- City, State, Pincode -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">City</label>
                    <input type="text" 
                           name="city" 
                           value="{{ old('city', $customer->city) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">State</label>
                    <input type="text" 
                           name="state" 
                           value="{{ old('state', $customer->state) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pincode</label>
                    <input type="text" 
                           name="pincode" 
                           value="{{ old('pincode', $customer->pincode) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>
            </div>

            <!-- Country -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Country</label>
                <input type="text" 
                       name="country" 
                       value="{{ old('country', $customer->country ?? 'India') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <button type="submit" 
                            class="px-6 py-3 text-white font-medium rounded-lg"
                            style="background: #f2601f;">
                        <i class="fas fa-save mr-2"></i>
                        Update Customer
                    </button>
                    <a href="{{ route('customers.show', $customer) }}" 
                       class="px-6 py-3 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300">
                        Cancel
                    </a>
                </div>

                <!-- Delete Button -->
                @if($customer->orders()->count() == 0)
                    <form method="POST" 
                          action="{{ route('customers.destroy', $customer) }}"
                          onsubmit="return confirm('Are you sure you want to delete this customer?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg">
                            <i class="fas fa-trash mr-2"></i>
                            Delete
                        </button>
                    </form>
                @endif
            </div>
        </form>
    </div>
</div>
@endsection