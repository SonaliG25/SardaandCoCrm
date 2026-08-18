@extends('layouts.app')

@section('title', 'Add Shipping Partner')

@section('page-title', 'Add Shipping Partner')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center space-x-3 mb-2">
            <a href="{{ route('shipping-partners.index') }}" class="text-gray-600 hover:text-gray-900">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="text-2xl font-bold text-gray-900">Add New Shipping Partner</h1>
        </div>
        <p class="text-gray-600">Create a new courier or logistics partner</p>
    </div>

    <form action="{{ route('shipping-partners.store') }}" method="POST">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Form -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Information -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="bg-gradient-to-r from-sarda-600 to-sarda-700 px-6 py-4">
                        <h3 class="text-lg font-semibold text-white">
                            <i class="fas fa-info-circle mr-2"></i>Basic Information
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <!-- Partner Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Partner Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="name" 
                                   value="{{ old('name') }}"
                                   required
                                   placeholder="e.g., Delhivery, DTDC, Blue Dart"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sarda-500 focus:border-sarda-500">
                            @error('name')
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
                                   value="{{ old('contact_person') }}"
                                   placeholder="Primary contact name"
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
                                   value="{{ old('phone') }}"
                                   placeholder="+91 1234567890"
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
                                   value="{{ old('email') }}"
                                   placeholder="contact@partner.com"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sarda-500 focus:border-sarda-500">
                            @error('email')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
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
                        <!-- API Key -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                API Key
                            </label>
                            <input type="text" 
                                   name="api_key" 
                                   value="{{ old('api_key') }}"
                                   placeholder="Enter API key for tracking integration"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sarda-500 focus:border-sarda-500">
                            @error('api_key')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-gray-500 mt-1">API key for automated tracking updates</p>
                        </div>

                        <!-- API Secret -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                API Secret / Token
                            </label>
                            <input type="password" 
                                   name="api_secret" 
                                   value="{{ old('api_secret') }}"
                                   placeholder="Enter API secret or access token"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sarda-500 focus:border-sarda-500">
                            @error('api_secret')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-gray-500 mt-1">Keep this confidential</p>
                        </div>

                        <!-- Tracking URL -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tracking URL
                            </label>
                            <input type="url" 
                                   name="tracking_url" 
                                   value="{{ old('tracking_url') }}"
                                   placeholder="https://track.partner.com/track?awb="
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sarda-500 focus:border-sarda-500">
                            @error('tracking_url')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-gray-500 mt-1">Base URL for tracking shipments</p>
                        </div>

                        <!-- Active Status -->
                        <div class="pt-4 border-t">
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="is_active" 
                                       value="1"
                                       {{ old('is_active', true) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-sarda-600 focus:ring-sarda-500">
                                <span class="ml-2 text-sm text-gray-700">Partner is active and available for new shipments</span>
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
                            Create Partner
                        </button>

                        <a href="{{ route('shipping-partners.index') }}" class="w-full px-4 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition text-center block">
                            <i class="fas fa-times mr-2"></i>
                            Cancel
                        </a>
                    </div>
                </div>

                <!-- Help Card -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h4 class="font-semibold text-blue-900 mb-2 flex items-center">
                        <i class="fas fa-info-circle mr-2"></i>
                        API Integration
                    </h4>
                    <ul class="text-sm text-blue-800 space-y-1">
                        <li>• API credentials enable automatic tracking</li>
                        <li>• You can add API details later</li>
                        <li>• Partners work without API integration</li>
                        <li>• Manual AWB entry always available</li>
                    </ul>
                </div>

                <!-- Popular Partners -->
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <h4 class="font-semibold text-green-900 mb-2 flex items-center">
                        <i class="fas fa-star mr-2"></i>
                        Popular Partners
                    </h4>
                    <ul class="text-sm text-green-800 space-y-1">
                        <li>• Delhivery</li>
                        <li>• DTDC</li>
                        <li>• Blue Dart</li>
                        <li>• FedEx</li>
                        <li>• DHL</li>
                    </ul>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection