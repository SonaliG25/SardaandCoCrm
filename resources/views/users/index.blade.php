@extends('layouts.app')

@section('title', 'Users Management')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-3xl font-bold text-gray-900">Users Management</h1>
        <a href="{{ route('users.create') }}" class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg">
            <i class="fas fa-plus mr-2"></i> Create User
        </a>
    </div>

    @if (session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($users as $user)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ $user->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $user->email }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $user->phone ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
                            {{ $user->role?->name ?? 'No Role' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if ($user->is_active)
                            <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm">Active</span>
                        @else
                            <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm">Inactive</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right whitespace-nowrap text-sm space-x-2">
                        <a href="{{ route('users.edit', $user) }}" class="text-blue-600 hover:text-blue-900">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        
                        <form method="POST" action="{{ route('users.toggleStatus', $user) }}" style="display:inline;">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="text-yellow-600 hover:text-yellow-900">
                                <i class="fas fa-toggle-{{ $user->is_active ? 'on' : 'off' }}"></i>
                            </button>
                        </form>

                        <form method="POST" action="{{ route('users.resetPassword', $user) }}" style="display:inline;" onsubmit="return confirm('Reset password for this user?')">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="text-purple-600 hover:text-purple-900">
                                <i class="fas fa-key"></i> Reset
                            </button>
                        </form>

                        <form method="POST" action="{{ route('users.destroy', $user) }}" style="display:inline;" onsubmit="return confirm('Delete this user?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                        No users found. <a href="{{ route('users.create') }}" class="text-orange-600 hover:underline">Create one</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $users->links() }}
    </div>
</div>
@endsection