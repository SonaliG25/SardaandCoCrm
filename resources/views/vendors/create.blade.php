@extends('layouts.app')

@section('title', 'Add New Vendor')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center space-x-3">
            <a href="{{ route('vendors.index') }}" 
               class="text-gray-600 hover:text-gray-900">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Add New Vendor</h1>
                <p class="text-gray-600 mt-1">Create a new vendor for your workflow stages</p>
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Form -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <form method="POST" action="{{ route('vendors.store') }}">
                    @csrf

                    <!-- Vendor Name -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Vendor Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="name" 
                               value="{{ old('name') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-opacity-50"
                               style="focus:ring-color: #f2601f;"
                               placeholder="Enter vendor name"
                               required>
                    </div>

                    <!-- Vendor Type -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            Vendor Type <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <!-- Dye -->
                            <label class="relative cursor-pointer">
                                <input type="radio" 
                                       name="type" 
                                       value="dye" 
                                       class="peer sr-only"
                                       {{ old('type') == 'dye' ? 'checked' : '' }}
                                       required>
                                <div class="p-4 border-2 border-gray-200 rounded-lg text-center transition peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:border-blue-300">
                                    <i class="fas fa-tint text-3xl text-blue-500 mb-2"></i>
                                    <p class="font-medium text-gray-900">Dye</p>
                                </div>
                            </label>

                            <!-- Print -->
                            <label class="relative cursor-pointer">
                                <input type="radio" 
                                       name="type" 
                                       value="print" 
                                       class="peer sr-only"
                                       {{ old('type') == 'print' ? 'checked' : '' }}>
                                <div class="p-4 border-2 border-gray-200 rounded-lg text-center transition peer-checked:border-green-500 peer-checked:bg-green-50 hover:border-green-300">
                                    <i class="fas fa-print text-3xl text-green-500 mb-2"></i>
                                    <p class="font-medium text-gray-900">Print</p>
                                </div>
                            </label>

                            <!-- Embroidery -->
                            <label class="relative cursor-pointer">
                                <input type="radio" 
                                       name="type" 
                                       value="emb" 
                                       class="peer sr-only"
                                       {{ old('type') == 'emb' ? 'checked' : '' }}>
                                <div class="p-4 border-2 border-gray-200 rounded-lg text-center transition peer-checked:border-purple-500 peer-checked:bg-purple-50 hover:border-purple-300">
                                    <i class="fas fa-cut text-3xl text-purple-500 mb-2"></i>
                                    <p class="font-medium text-gray-900">Embroidery</p>
                                </div>
                            </label>

                            <!-- Master -->
                            <label class="relative cursor-pointer">
                                <input type="radio" 
                                       name="type" 
                                       value="master" 
                                       class="peer sr-only"
                                       {{ old('type') == 'master' ? 'checked' : '' }}>
                                <div class="p-4 border-2 border-gray-200 rounded-lg text-center transition peer-checked:border-orange-500 peer-checked:bg-orange-50 hover:border-orange-300">
                                    <i class="fas fa-user-tie text-3xl text-orange-500 mb-2"></i>
                                    <p class="font-medium text-gray-900">Master</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Contact Person -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Contact Person</label>
                        <input type="text" 
                               name="contact_person" 
                               value="{{ old('contact_person') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                               placeholder="Enter contact person name">
                    </div>

                    <!-- Phone Number -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                        <input type="text" 
                               name="phone" 
                               value="{{ old('phone') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                               placeholder="+91 1234567890">
                    </div>

                    <!-- Email Address -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                        <input type="email" 
                               name="email" 
                               value="{{ old('email') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                               placeholder="vendor@example.com">
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="fas fa-info-circle mr-1"></i>
                            If email is provided, vendor portal login will be automatically created
                        </p>
                    </div>

                    <!-- Vendor is Active -->
                    <div class="mb-6">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" 
                                   name="is_active" 
                                   value="1"
                                   {{ old('is_active', true) ? 'checked' : '' }}
                                   class="w-4 h-4 rounded border-gray-300"
                                   style="color: #f2601f;">
                            <span class="ml-2 text-sm font-medium text-gray-700">Vendor is active</span>
                        </label>
                        <p class="text-xs text-gray-500 mt-1 ml-6">Active vendors can be assigned to orders</p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center space-x-3 pt-6 border-t">
                        <button type="submit" 
                                class="flex items-center px-6 py-3 text-white font-medium rounded-lg shadow-lg hover:opacity-90 transition"
                                style="background: #f2601f;">
                            <i class="fas fa-save mr-2"></i>
                            Create Vendor
                        </button>
                        <a href="{{ route('vendors.index') }}" 
                           class="px-6 py-3 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300 transition">
                            <i class="fas fa-times mr-2"></i>
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="lg:col-span-1">
            <!-- Vendor Types Info -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                <h3 class="text-sm font-semibold text-blue-900 mb-3 flex items-center">
                    <i class="fas fa-info-circle mr-2"></i>
                    Vendor Types
                </h3>
                <ul class="space-y-2 text-sm text-blue-800">
                    <li class="flex items-start">
                        <i class="fas fa-tint text-blue-600 mr-2 mt-1"></i>
                        <div>
                            <strong>Dye:</strong> Fabric dyeing vendors
                        </div>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-print text-green-600 mr-2 mt-1"></i>
                        <div>
                            <strong>Print:</strong> Printing vendors
                        </div>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-cut text-purple-600 mr-2 mt-1"></i>
                        <div>
                            <strong>Emb:</strong> Embroidery vendors
                        </div>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-user-tie text-orange-600 mr-2 mt-1"></i>
                        <div>
                            <strong>Master:</strong> Master tailors
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Portal Login Info -->
            <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                <h3 class="text-sm font-semibold text-green-900 mb-3 flex items-center">
                    <i class="fas fa-user-lock mr-2"></i>
                    Vendor Portal Login
                </h3>
                <div class="text-sm text-green-800 space-y-2">
                    <p>
                        <i class="fas fa-check-circle text-green-600 mr-1"></i>
                        If email is provided, login credentials will be auto-generated
                    </p>
                    <p>
                        <i class="fas fa-key text-green-600 mr-1"></i>
                        Default password: <code class="bg-green-100 px-2 py-1 rounded">vendor123</code>
                    </p>
                    <p>
                        <i class="fas fa-lock text-green-600 mr-1"></i>
                        Vendor should change password after first login
                    </p>
                    <p class="pt-2 border-t border-green-200">
                        <i class="fas fa-info-circle text-green-600 mr-1"></i>
                        You can also create login later from vendor details page
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Radio button animation */
input[type="radio"]:checked + div {
    transform: scale(1.02);
}

input[type="radio"] + div {
    transition: all 0.2s ease;
}
</style>
<style>
/* Selected state - Orange gradient */
input[type="radio"]:checked + div {
    background: linear-gradient(to bottom right, #f2601f, #e34715);
    border-color: #e34715;
    transform: scale(1.05);
    box-shadow: 0 10px 15px -3px rgba(242, 96, 31, 0.3);
}

input[type="radio"]:checked + div i,
input[type="radio"]:checked + div p {
    color: white !important;
}

/* Smooth transitions */
input[type="radio"] + div {
    transition: all 0.3s ease;
}

/* Hover effect */
input[type="radio"] + div:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

/* Keep scale when selected and hovered */
input[type="radio"]:checked + div:hover {
    transform: scale(1.05);
}
</style>
@endsection