<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        if (!auth()->user()->hasMenuAccess('users')) {
            abort(403, 'You do not have access to this section.');
        }

        // Show all users EXCEPT vendors
        $users = User::where('user_type', '!=', 'vendor')
                    ->with('role')
                    ->paginate(20);
        
        return view('users.index', compact('users'));
    }

    public function create()
    {
        if (!auth()->user()->hasMenuAccess('users')) {
            abort(403, 'You do not have access to this section.');
        }

        $roles = Role::where('is_system', false)
                    ->orWhere('is_system', true)
                    ->get();
        
        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasMenuAccess('users')) {
            abort(403, 'You do not have access to this section.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'user_type' => 'required|in:admin,vendor,manager,staff,guest',
            'role_id' => 'required|exists:roles,id',  // ✅ Always required
        ]);

        // ✅ Create with all fields
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'user_type' => $validated['user_type'],
            'role_id' => $validated['role_id'],  // ✅ This will be stored
        ]);

        // ✅ Reload with relationship
        $user->load('role');

        ActivityLogService::log(
            'created',
            'users',
            $user->id,
            'User',
            "Created {$user->user_type} user: {$user->name} with role: {$user->role?->name}"
        );

        return redirect()->route('users.index')->with('success', 'User created successfully!');
    }

    public function edit(User $user)
    {
        if (!auth()->user()->hasMenuAccess('users')) {
            abort(403, 'You do not have access to this section.');
        }

        // ✅ Only block editing for actual vendor users
        // Allow: admin, manager, staff, guest
        if ($user->user_type === 'vendor') {
            return redirect()->route('users.index')
                ->with('error', 'Cannot edit vendor users here. Manage vendors in the Vendors section instead.');
        }

        $roles = Role::all();
        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        if (!auth()->user()->hasMenuAccess('users')) {
            abort(403, 'You do not have access to this section.');
        }

        // ✅ Only block editing for actual vendor users
        if ($user->user_type === 'vendor') {
            return redirect()->route('users.index')
                ->with('error', 'Cannot edit vendor users here.');
        }

        $oldData = $user->toArray();
        $oldRole = $user->role?->name ?? 'No Role';

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'role_id' => 'required|exists:roles,id',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'role_id' => $validated['role_id'],
        ]);

        $newRole = $user->role?->name ?? 'No Role';

        $changes = [];
        foreach ($oldData as $key => $oldValue) {
            if ($key != 'updated_at' && $user->$key != $oldValue) {
                $changes[] = "$key: $oldValue → " . $user->$key;
            }
        }

        if ($oldRole !== $newRole) {
            $changes[] = "role: $oldRole → $newRole";
        }

        ActivityLogService::log(
            'edited',
            'users',
            $user->id,
            'User',
            "Updated user: {$user->name}. " . implode(', ', $changes),
            $oldData,
            $user->toArray()
        );

        return redirect()->route('users.index')->with('success', 'User updated successfully!');
    }

    public function toggleStatus(User $user)
    {
        if (!auth()->user()->hasMenuAccess('users')) {
            abort(403, 'You do not have access to this section.');
        }

        // ✅ Only block for actual vendor users
        if ($user->user_type === 'vendor') {
            return redirect()->route('users.index')->with('error', 'Cannot manage vendor users here.');
        }

        $oldStatus = $user->is_active ? 'Active' : 'Inactive';
        $user->is_active = !$user->is_active;
        $user->save();
        $newStatus = $user->is_active ? 'Active' : 'Inactive';

        ActivityLogService::log(
            'edited',
            'users',
            $user->id,
            'User',
            "Changed status for user: {$user->name} from $oldStatus to $newStatus"
        );

        return redirect()->route('users.index')->with('success', 'User status updated!');
    }

    public function resetPassword(User $user)
    {
        if (!auth()->user()->hasMenuAccess('users')) {
            abort(403, 'You do not have access to this section.');
        }

        // ✅ Only block for actual vendor users
        if ($user->user_type === 'vendor') {
            return redirect()->route('users.index')->with('error', 'Cannot manage vendor users here.');
        }

        $newPassword = 'Password@123';
        $user->password = Hash::make($newPassword);
        $user->save();

        ActivityLogService::log(
            'edited',
            'users',
            $user->id,
            'User',
            "Reset password for user: {$user->name}"
        );

        return redirect()->route('users.index')->with('success', "Password reset to: $newPassword");
    }

    public function destroy(User $user)
    {
        if (!auth()->user()->hasMenuAccess('users')) {
            abort(403, 'You do not have access to this section.');
        }

        // ✅ Only block for actual vendor users
        if ($user->user_type === 'vendor') {
            return redirect()->route('users.index')->with('error', 'Cannot delete vendor users here.');
        }

        $userName = $user->name;
        
        ActivityLogService::log(
            'deleted',
            'users',
            $user->id,
            'User',
            "Deleted user: {$userName}"
        );

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully!');
    }
}

// namespace App\Http\Controllers;

// use App\Models\User;
// use App\Models\Role;
// use App\Services\ActivityLogService;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Hash;

// class UserController extends Controller
// {
//     // Show all users
//   public function index()
// {
//     if (!auth()->user()->hasMenuAccess('users')) {
//         abort(403, 'You do not have access to this section.');
//     }

//     // ✅ Show all users EXCEPT vendors
//     $users = User::where('user_type', '!=', 'vendor')  // ← Exclude vendors only
//                 ->with('role')
//                 ->paginate(20);
    
//     return view('users.index', compact('users'));
// }

//     // Show create form
//     public function create()
//     {
//         $roles = Role::where('is_system', false)->orWhere('is_system', true)->get();
//         return view('users.create', compact('roles'));
//     }

//  public function store(Request $request)
// {
//     if (!auth()->user()->hasMenuAccess('users')) {
//         abort(403, 'You do not have access to this section.');
//     }

//     $validated = $request->validate([
//         'name' => 'required|string|max:255',
//         'email' => 'required|email|unique:users,email',
//         'password' => 'required|string|min:8|confirmed',
//         'phone' => 'nullable|string|max:20',
//         'user_type' => 'required|in:admin,vendor,manager,staff,guest',  // ✅ Add all types
//         'role_id' => 'required|exists:roles,id',
//     ]);

//     // ✅ Create user with all fields
//     $user = User::create([
//         'name' => $validated['name'],
//         'email' => $validated['email'],
//         'password' => Hash::make($validated['password']),
//         'phone' => $validated['phone'] ?? null,
//         'user_type' => $validated['user_type'],  // ✅ Store user_type
//         'role_id' => $validated['role_id'],       // ✅ Store role_id
//     ]);

//     // ✅ Reload user with role
//     $user = $user->fresh('role');

//     // Log user creation
//     ActivityLogService::log(
//         'created',
//         'users',
//         $user->id,
//         'User',
//         "Created {$user->user_type} user: {$user->name} with role: {$user->role?->name}"
//     );

//     return redirect()->route('users.index')->with('success', 'User created successfully!');
// }

//     // Show user details
//     public function show(User $user)
//     {
//         return view('users.show', compact('user'));
//     }

//     // Show edit form
//   public function edit(User $user)
// {
//     if (!auth()->user()->hasMenuAccess('users')) {
//         abort(403, 'You do not have access to this section.');
//     }

//     // ✅ Cannot edit vendor users
//     if ($user->user_type === 'vendor') {
//         return redirect()->route('users.index')
//             ->with('error', 'Cannot edit vendor users here. Manage vendors in the Vendors section instead.');
//     }

//     $roles = Role::all();
//     return view('users.edit', compact('user', 'roles'));
// }

// public function update(Request $request, User $user)
// {
//     if (!auth()->user()->hasMenuAccess('users')) {
//         abort(403, 'You do not have access to this section.');
//     }

//     // ✅ Cannot edit vendor users
//     if ($user->user_type === 'vendor') {
//         return redirect()->route('users.index')
//             ->with('error', 'Cannot edit vendor users here.');
//     }

//     $oldData = $user->toArray();
//     $oldRole = $user->role?->name ?? 'No Role';

//     $validated = $request->validate([
//         'name' => 'required|string|max:255',
//         'email' => 'required|email|unique:users,email,' . $user->id,
//         'phone' => 'nullable|string|max:20',
//         'role_id' => 'required|exists:roles,id',
//     ]);

//     $user->update([
//         'name' => $validated['name'],
//         'email' => $validated['email'],
//         'phone' => $validated['phone'] ?? null,
//         'role_id' => $validated['role_id'],
//     ]);

//     $newRole = $user->role?->name ?? 'No Role';

//     $changes = [];
//     foreach ($oldData as $key => $oldValue) {
//         if ($key != 'updated_at' && $user->$key != $oldValue) {
//             $changes[] = "$key: $oldValue → " . $user->$key;
//         }
//     }

//     if ($oldRole !== $newRole) {
//         $changes[] = "role: $oldRole → $newRole";
//     }

//     ActivityLogService::log(
//         'edited',
//         'users',
//         $user->id,
//         'User',
//         "Updated user: {$user->name}. " . implode(', ', $changes),
//         $oldData,
//         $user->toArray()
//     );

//     return redirect()->route('users.index')->with('success', 'User updated successfully!');
// }

//     // Toggle user status
//     public function toggleStatus(User $user)
//     {
//         $oldStatus = $user->is_active ? 'Active' : 'Inactive';
//         $user->is_active = !$user->is_active;
//         $user->save();
//         $newStatus = $user->is_active ? 'Active' : 'Inactive';

//         ActivityLogService::log(
//             'edited',
//             'users',
//             $user->id,
//             'User',
//             "Changed status for user: {$user->name} from $oldStatus to $newStatus"
//         );

//         return redirect()->route('users.index')->with('success', 'User status updated!');
//     }

//     // Reset password
//     public function resetPassword(User $user)
//     {
//         $newPassword = 'Password@123';
//         $user->password = Hash::make($newPassword);
//         $user->save();

//         ActivityLogService::log(
//             'edited',
//             'users',
//             $user->id,
//             'User',
//             "Reset password for user: {$user->name}"
//         );

//         return redirect()->route('users.index')->with('success', "Password reset to: $newPassword");
//     }

//     // Delete user
//     public function destroy(User $user)
//     {
//         $userName = $user->name;
        
//         ActivityLogService::log(
//             'deleted',
//             'users',
//             $user->id,
//             'User',
//             "Deleted user: {$userName}"
//         );

//         $user->delete();

//         return redirect()->route('users.index')->with('success', 'User deleted successfully!');
//     }
// }