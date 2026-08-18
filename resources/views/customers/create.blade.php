@extends('layouts.app')

@section('title', 'Add New Customer')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center space-x-3">
            <a href="{{ route('customers.index') }}" 
               class="text-gray-600 hover:text-gray-900">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Add New Customer</h1>
                <p class="text-gray-600 mt-1">Create a new customer record</p>
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
        <form method="POST" action="{{ route('customers.store') }}">
            @csrf

            <!-- Name -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Full Name <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="name" 
                       value="{{ old('name') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                       placeholder="Enter customer name"
                       required>
            </div>

            <!-- Email -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                <input type="email" 
                       name="email" 
                       value="{{ old('email') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                       placeholder="customer@example.com">
            </div>

            <!-- Phone -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Phone Number <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="phone" 
                       value="{{ old('phone') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                       placeholder="+91 1234567890"
                       required>
            </div>

            <!-- Address -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                <textarea name="address" 
                          rows="3"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                          placeholder="Street address">{{ old('address') }}</textarea>
            </div>

            <!-- City, State, Pincode -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">City</label>
                    <input type="text" 
                           name="city" 
                           value="{{ old('city') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                           placeholder="City">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">State</label>
                    <input type="text" 
                           name="state" 
                           value="{{ old('state') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                           placeholder="State">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pincode</label>
                    <input type="text" 
                           name="pincode" 
                           value="{{ old('pincode') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                           placeholder="123456">
                </div>
            </div>

            <!-- Country -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Country</label>
                <input type="text" 
                       name="country" 
                       value="{{ old('country', 'India') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>

            <!-- Actions -->
            <div class="flex items-center space-x-3">
                <button type="submit" 
                        class="px-6 py-3 text-white font-medium rounded-lg"
                        style="background: #f2601f;">
                    <i class="fas fa-save mr-2"></i>
                    Create Customer
                </button>
                <a href="{{ route('customers.index') }}" 
                   class="px-6 py-3 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection