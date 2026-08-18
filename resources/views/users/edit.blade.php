@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Edit User: {{ $user->name }}</h1>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('users.update', $user) }}">
            @csrf
            @method('PUT')

            <!-- Name -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Full Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500" 
                    required>
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Email <span class="text-red-500">*</span>
                </label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500" 
                    required>
                @error('email')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Phone -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500">
                @error('phone')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- User Type Badge -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">User Type</label>
                <div class="px-4 py-2 bg-gray-100 rounded-lg text-sm font-medium">
                    @if ($user->user_type === 'admin')
                        <span class="text-blue-600">👤 Admin User</span>
                    @elseif ($user->user_type === 'manager')
                        <span class="text-purple-600">📋 Manager User</span>
                    @elseif ($user->user_type === 'staff')
                        <span class="text-green-600">👨‍💼 Staff User</span>
                    @elseif ($user->user_type === 'guest')
                        <span class="text-gray-600">👤 Guest User</span>
                    @else
                        <span>{{ ucfirst($user->user_type) }}</span>
                    @endif
                </div>
                <p class="text-xs text-gray-500 mt-1">User type cannot be changed. Create a new user if you need a different type.</p>
            </div>

            <!-- Role Dropdown -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Role <span class="text-red-500">*</span>
                </label>
                <select name="role_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500" required>
                    <option value="">Select a role...</option>
                    @foreach ($roles as $role)
                    <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                        {{ $role->name }}
                        @if ($role->is_system)
                            (System)
                        @endif
                    </option>
                    @endforeach
                </select>
                @error('role_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Status -->
            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" {{ $user->is_active ? 'checked' : '' }} disabled 
                        class="rounded border-gray-300">
                    <span class="ml-3 text-sm font-medium text-gray-700">
                        Status: {{ $user->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </label>
                <p class="text-xs text-gray-500 mt-2">Use the toggle button on the users list to change status</p>
            </div>

            <!-- Buttons -->
            <div class="flex gap-4">
                <button type="submit" class="px-6 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg font-medium">
                    <i class="fas fa-save mr-2"></i> Update User
                </button>
                <a href="{{ route('users.index') }}" class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg font-medium">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection