@extends('layouts.app')

@section('title', 'Create User')

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Create New User</h1>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('users.store') }}" id="createUserForm">
            @csrf

            <!-- Name -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Full Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" value="{{ old('name') }}" 
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
                <input type="email" name="email" value="{{ old('email') }}" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500" 
                    required>
                @error('email')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Phone -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                <input type="tel" name="phone" value="{{ old('phone') }}" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500">
                @error('phone')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Password <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input type="text" name="password" id="password"
                        value="{{ old('password', 'test@12345') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 pr-10" 
                        required>
                    <button type="button" onclick="togglePassword('password')" 
                        class="absolute right-3 top-2.5 text-gray-500 hover:text-gray-700">
                        <!--<i class="fas fa-eye" id="passwordIcon"></i>-->
                    </button>
                </div>
                <p class="text-xs text-gray-500 mt-1">Minimum 8 characters</p>
                @error('password')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Confirm Password -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Confirm Password <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input type="text" name="password_confirmation" id="password_confirmation"
                        value="{{ old('password_confirmation', 'test@12345') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 pr-10" 
                        required>
                    <button type="button" onclick="togglePassword('password_confirmation')" 
                        class="absolute right-3 top-2.5 text-gray-500 hover:text-gray-700">
                        <!--<i class="fas fa-eye" id="password_confirmationIcon"></i>-->
                    </button>
                </div>
                @error('password_confirmation')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- User Type -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    User Type <span class="text-red-500">*</span>
                </label>
                <select name="user_type" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500" 
                    required>
                    <option value="">Select user type...</option>
                    <option value="admin" {{ old('user_type') == 'admin' ? 'selected' : '' }}>
                        Admin
                    </option>
                  
                    <option value="manager" {{ old('user_type') == 'manager' ? 'selected' : '' }}>
                        Manager
                    </option>
                    <option value="staff" {{ old('user_type') == 'staff' ? 'selected' : '' }}>
                        Staff
                    </option>
                    <option value="guest" {{ old('user_type') == 'guest' ? 'selected' : '' }}>
                        Guest
                    </option>
                </select>
                @error('user_type')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Role -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Role <span class="text-red-500">*</span>
                </label>
                <select name="role_id" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500" 
                    required>
                    <option value="">Select a role...</option>
                    @foreach ($roles as $role)
                    <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                        {{ $role->name }}
                    </option>
                    @endforeach
                </select>
                @error('role_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Buttons -->
            <div class="flex gap-4">
                <button type="submit" class="px-6 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg font-medium">
                    <i class="fas fa-save mr-2"></i> Create User
                </button>
                <a href="{{ route('users.index') }}" class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg font-medium">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + 'Icon');
    
    // Toggle between text and password
    if (field.type === 'text') {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    } else {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    }
}
</script>
@endsection