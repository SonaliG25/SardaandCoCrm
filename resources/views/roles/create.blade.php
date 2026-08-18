@extends('layouts.app')

@section('title', 'Create Role')

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Create New Role</h1>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('roles.store') }}">
            @csrf

            <!-- Role Name -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Role Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500" placeholder="e.g., Sales Manager, Warehouse Staff" required>
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea name="description" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500" placeholder="e.g., Manages sales orders and customer information"></textarea>
            </div>

            <!-- Menu Access -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-4">
                    Menu Access <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-1 gap-3">
                    @foreach ($menus as $key => $label)
                    <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-orange-50 cursor-pointer">
                        <input type="checkbox" name="menu_access[{{ $key }}]" value="1" class="rounded border-gray-300 text-orange-600">
                        <span class="ml-3 font-medium text-gray-700">{{ $label }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <!-- Buttons -->
            <div class="flex gap-4">
                <button type="submit" class="px-6 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg font-medium">
                    <i class="fas fa-save mr-2"></i> Create Role
                </button>
                <a href="{{ route('roles.index') }}" class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg font-medium">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection