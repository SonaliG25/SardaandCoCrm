@extends('layouts.app')

@section('title', 'Edit Vendor - ' . $vendor->name)

@section('page-title', 'Edit Vendor')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center space-x-3 mb-2">
            <a href="{{ route('vendors.show', $vendor) }}" class="text-gray-600 hover:text-gray-900">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="text-2xl font-bold text-gray-900">Edit Vendor</h1>
        </div>
        <p class="text-gray-600">Update vendor information</p>
    </div>

    <form action="{{ route('vendors.update', $vendor) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Form -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="bg-gradient-to-r from-sarda-600 to-sarda-700 px-6 py-4">
                        <h3 class="text-lg font-semibold text-white">
                            <i class="fas fa-info-circle mr-2"></i>Vendor Information
                        </h3>
                    </div>
                    <div class="p-6 space-y-6">
                        <!-- Vendor Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Vendor Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="name" 
                                   value="{{ old('name', $vendor->name) }}"
                                   required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sarda-500 focus:border-sarda-500">
                            @error('name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Vendor Type -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Vendor Type <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <label class="relative flex items-center justify-center p-4 border-2 rounded-lg cursor-pointer transition-all hover:border-sarda-500 has-[:checked]:border-sarda-600 has-[:checked]:bg-sarda-50">
                                    <input type="radio" name="type" value="dye" {{ old('type', $vendor->type) == 'dye' ? 'checked' : '' }} required class="sr-only">
                                    <div class="text-center">
                                        <i class="fas fa-tint text-2xl text-blue-600 mb-2"></i>
                                        <div class="text-sm font-medium">Dye</div>
                                    </div>
                                </label>

                                <label class="relative flex items-center justify-center p-4 border-2 rounded-lg cursor-pointer transition-all hover:border-sarda-500 has-[:checked]:border-sarda-600 has-[:checked]:bg-sarda-50">
                                    <input type="radio" name="type" value="print" {{ old('type', $vendor->type) == 'print' ? 'checked' : '' }} required class="sr-only">
                                    <div class="text-center">
                                        <i class="fas fa-print text-2xl text-green-600 mb-2"></i>
                                        <div class="text-sm font-medium">Print</div>
                                    </div>
                                </label>

                                <label class="relative flex items-center justify-center p-4 border-2 rounded-lg cursor-pointer transition-all hover:border-sarda-500 has-[:checked]:border-sarda-600 has-[:checked]:bg-sarda-50">
                                    <input type="radio" name="type" value="emb" {{ old('type', $vendor->type) == 'emb' ? 'checked' : '' }} required class="sr-only">
                                    <div class="text-center">
                                        <i class="fas fa-cut text-2xl text-purple-600 mb-2"></i>
                                        <div class="text-sm font-medium">Embroidery</div>
                                    </div>
                                </label>

                                <label class="relative flex items-center justify-center p-4 border-2 rounded-lg cursor-pointer transition-all hover:border-sarda-500 has-[:checked]:border-sarda-600 has-[:checked]:bg-sarda-50">
                                    <input type="radio" name="type" value="master" {{ old('type', $vendor->type) == 'master' ? 'checked' : '' }} required class="sr-only">
                                    <div class="text-center">
                                        <i class="fas fa-user-tie text-2xl text-orange-600 mb-2"></i>
                                        <div class="text-sm font-medium">Master</div>
                                    </div>
                                </label>
                            </div>
                            @error('type')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Contact Person -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Contact Person
                            </label>
                            <input type="text" 
                                   name="contact_person" 
                                   value="{{ old('contact_person', $vendor->contact_person) }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sarda-500 focus:border-sarda-500">
                            @error('contact_person')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Phone -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Phone Number
                            </label>
                            <input type="text" 
                                   name="phone" 
                                   value="{{ old('phone', $vendor->phone) }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sarda-500 focus:border-sarda-500">
                            @error('phone')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Email Address
                            </label>
                            <input type="email" 
                                   name="email" 
                                   value="{{ old('email', $vendor->email) }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sarda-500 focus:border-sarda-500">
                            @error('email')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Active Status -->
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="is_active" 
                                       value="1"
                                       {{ old('is_active', $vendor->is_active) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-sarda-600 focus:ring-sarda-500">
                                <span class="ml-2 text-sm text-gray-700">Vendor is active</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Action Buttons -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden sticky top-6">
                    <div class="p-6 space-y-3">
                        <button type="submit" class="w-full px-4 py-3 bg-sarda-600 hover:bg-sarda-700 text-white font-semibold rounded-lg shadow-lg transition transform hover:scale-105">
                            <i class="fas fa-save mr-2"></i>
                            Update Vendor
                        </button>

                        <a href="{{ route('vendors.show', $vendor) }}" class="w-full px-4 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition text-center block">
                            <i class="fas fa-times mr-2"></i>
                            Cancel
                        </a>

                        <a href="{{ route('vendors.index') }}" class="w-full px-4 py-3 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium rounded-lg transition text-center block">
                            <i class="fas fa-list mr-2"></i>
                            Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection