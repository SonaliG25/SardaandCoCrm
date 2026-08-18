<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    // Show all roles
    public function index()
    {
        $roles = Role::withCount('users')->get();
        return view('roles.index', compact('roles'));
    }

    // Show create form
    public function create()
    {
        $menus = [
            'dashboard' => 'Dashboard',
            'orders' => 'Orders',
            'customers' => 'Customers',
            'vendors' => 'Vendors',
            'reports' => 'Reports',
            'users' => 'Users',
            'roles' => 'Roles',
            'settings' => 'Settings',
        ];
        
        return view('roles.create', compact('menus'));
    }

    // Store role
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles',
            'description' => 'nullable|string',
            'menu_access' => 'required|array',
        ]);

        $menuAccess = [];
        foreach ($request->menu_access as $menu => $checked) {
            $menuAccess[$menu] = $checked === 'on' || $checked === '1' || $checked === true;
        }

        Role::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'menu_access' => $menuAccess,
        ]);

        return redirect()->route('roles.index')->with('success', 'Role created successfully!');
    }

    // Show edit form
    public function edit(Role $role)
    {
        if ($role->is_system) {
            return redirect()->route('roles.index')->with('error', 'Cannot edit system roles!');
        }

        $menus = [
            'dashboard' => 'Dashboard',
            'orders' => 'Orders',
            'customers' => 'Customers',
            'vendors' => 'Vendors',
            'products' => 'Products',
            'shipping' => 'Shipping',
            'reports' => 'Reports',
            'users' => 'Users',
            'roles' => 'Roles',
            'settings' => 'Settings',
        ];

        return view('roles.edit', compact('role', 'menus'));
    }

    // Update role
    public function update(Request $request, Role $role)
    {
        if ($role->is_system) {
            return redirect()->route('roles.index')->with('error', 'Cannot edit system roles!');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'description' => 'nullable|string',
            'menu_access' => 'required|array',
        ]);

        $menuAccess = [];
        foreach ($request->menu_access as $menu => $checked) {
            $menuAccess[$menu] = $checked === 'on' || $checked === '1' || $checked === true;
        }

        $role->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'menu_access' => $menuAccess,
        ]);

        return redirect()->route('roles.index')->with('success', 'Role updated successfully!');
    }

    // Delete role
    public function destroy(Role $role)
    {
        if ($role->is_system) {
            return redirect()->route('roles.index')->with('error', 'Cannot delete system roles!');
        }

        if ($role->users()->count() > 0) {
            return redirect()->route('roles.index')->with('error', 'Cannot delete role with assigned users!');
        }

        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Role deleted successfully!');
    }

    // Clone role
    public function clone(Role $role)
    {
        $newRole = $role->replicate();
        $newRole->name = $role->name . ' (Copy)';
        $newRole->save();

        return redirect()->route('roles.index')->with('success', 'Role cloned successfully!');
    }
}