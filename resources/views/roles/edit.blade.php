@extends('layouts.app')

@section('title', 'Edit Role')

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Edit Role: {{ $role->name }}</h1>

    @if ($role->is_system)
        <div class="mb-6 p-4 bg-yellow-100 border border-yellow-400 text-yellow-700 rounded">
            <i class="fas fa-lock mr-2"></i>
            <strong>System Role:</strong> This is a system role and cannot be edited or deleted.
        </div>
    @endif

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('roles.update', $role) }}">
            @csrf
            @method('PUT')

            <!-- Role Name -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Role Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" value="{{ old('name', $role->name) }}" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500" 
                    {{ $role->is_system ? 'disabled' : '' }}
                    required>
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea name="description" rows="3" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500"
                    {{ $role->is_system ? 'disabled' : '' }}>{{ old('description', $role->description) }}</textarea>
                @error('description')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Menu Access -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-4">
                    Menu Access <span class="text-red-500">*</span>
                </label>
                
                @if ($role->is_system)
                    <p class="text-sm text-gray-600 mb-4">
                        <strong>Super Admin</strong> has access to all menus automatically.
                    </p>
                @endif

                <div class="grid grid-cols-1 gap-3">
                    @foreach ($menus as $key => $label)
                    <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-orange-50 cursor-pointer {{ $role->is_system ? 'bg-gray-50' : '' }}">
                        <input type="checkbox" 
                            name="menu_access[{{ $key }}]" 
                            value="1" 
                            {{ ($role->menu_access[$key] ?? false) ? 'checked' : '' }}
                            {{ $role->is_system ? 'disabled' : '' }}
                            class="rounded border-gray-300 text-orange-600">
                        <span class="ml-3 font-medium text-gray-700">{{ $label }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <!-- Buttons -->
            <div class="flex gap-4">
                @if (!$role->is_system)
                    <button type="submit" class="px-6 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg font-medium">
                        <i class="fas fa-save mr-2"></i> Update Role
                    </button>
                @endif
                <a href="{{ route('roles.index') }}" class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg font-medium">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection