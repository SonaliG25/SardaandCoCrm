<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'user_type',
        'vendor_id',
        'role_id',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Get user role (for role-based access control)
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get vendor profile (if user_type = 'vendor')
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get user activity logs
     */
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    // ========================================
    // USER TYPE CHECKS
    // ========================================

    /**
     * Check if user is admin type
     * @return bool
     */
    public function isAdmin()
    {
        return $this->user_type === 'admin';
    }

    /**
     * Check if user is vendor type
     * @return bool
     */
    public function isVendor()
    {
        return $this->user_type === 'vendor';
    }

    /**
     * Check if user is manager type
     * @return bool
     */
    public function isManager()
    {
        return $this->user_type === 'manager';
    }

    /**
     * Check if user is staff type
     * @return bool
     */
    public function isStaff()
    {
        return $this->user_type === 'staff';
    }

    /**
     * Check if user is guest type
     * @return bool
     */
    public function isGuest()
    {
        return $this->user_type === 'guest';
    }

    // ========================================
    // ROLE & PERMISSION CHECKS
    // ========================================

    /**
     * Check if user has specific menu access
     * @param string $menu
     * @return bool
     */
    public function hasMenuAccess($menu)
    {
        // If no role, no access
        if (!$this->role) {
            return false;
        }

        // Check if role has menu access
        return $this->role->hasMenuAccess($menu);
    }

    /**
     * Check if user has specific role
     * @param string|array $roles
     * @return bool
     */
    public function hasRole($roles)
    {
        if (is_string($roles)) {
            return $this->role?->name === $roles;
        }

        return in_array($this->role?->name, $roles);
    }

    /**
     * Check if user is super admin
     * @return bool
     */
    public function isSuperAdmin()
    {
        return $this->role?->is_system === true && $this->role?->name === 'Super Admin';
    }

    // ========================================
    // STATUS CHECKS
    // ========================================

    /**
     * Check if user is active
     * @return bool
     */
    public function isActive()
    {
        return $this->is_active === true;
    }

    /**
     * Check if user is inactive
     * @return bool
     */
    public function isInactive()
    {
        return $this->is_active === false;
    }

    /**
     * Activate user
     * @return bool
     */
    public function activate()
    {
        return $this->update(['is_active' => true]);
    }

    /**
     * Deactivate user
     * @return bool
     */
    public function deactivate()
    {
        return $this->update(['is_active' => false]);
    }

    /**
     * Toggle user active status
     * @return bool
     */
    public function toggleStatus()
    {
        return $this->update(['is_active' => !$this->is_active]);
    }

    // ========================================
    // UTILITY METHODS
    // ========================================

    /**
     * Get user full name
     * @return string
     */
    public function getFullName()
    {
        return $this->name;
    }

    /**
     * Get user role name
     * @return string|null
     */
    public function getRoleName()
    {
        return $this->role?->name ?? 'No Role';
    }

    /**
     * Get user type label
     * @return string
     */
    public function getUserTypeLabel()
    {
        return match($this->user_type) {
            'admin' => 'Admin',
            'vendor' => 'Vendor',
            'manager' => 'Manager',
            'staff' => 'Staff',
            'guest' => 'Guest',
            default => 'Unknown',
        };
    }

    /**
     * Get user status label
     * @return string
     */
    public function getStatusLabel()
    {
        return $this->is_active ? 'Active' : 'Inactive';
    }

    /**
     * Get all accessible menus for this user
     * @return array
     */
    public function getAccessibleMenus()
    {
        if (!$this->role) {
            return [];
        }

        return $this->role->getAccessibleMenus();
    }

    // ========================================
    // SCOPES
    // ========================================

    /**
     * Scope: Get only active users
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Get only inactive users
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope: Get only admin type users (excluding vendors)
     */
    public function scopeAdminOnly($query)
    {
        return $query->where('user_type', '!=', 'vendor');
    }

    /**
     * Scope: Get only vendor type users
     */
    public function scopeVendorOnly($query)
    {
        return $query->where('user_type', 'vendor');
    }

    /**
     * Scope: Get users with specific role
     */
    public function scopeWithRole($query, $roleName)
    {
        return $query->whereHas('role', function ($q) use ($roleName) {
            $q->where('name', $roleName);
        });
    }

    /**
     * Scope: Get users with specific user type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('user_type', $type);
    }

    /**
     * Scope: Search by name or email
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', "%{$search}%")
                     ->orWhere('email', 'like', "%{$search}%");
    }
}

// namespace App\Models;

// // use Illuminate\Contracts\Auth\MustVerifyEmail;
// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Foundation\Auth\User as Authenticatable;
// use Illuminate\Notifications\Notifiable;
// use Laravel\Sanctum\HasApiTokens;

// class User extends Authenticatable
// {
//     use HasApiTokens, HasFactory, Notifiable;

//         /**
//          * The attributes that are mass assignable.
//          *
//          * @var array<int, string>
//          */
//       protected $fillable = [
//         'name',
//         'email',
//         'password',
//         'phone',
//         'user_type',
//         'vendor_id',
//     ];

//     /**
//      * The attributes that should be hidden for serialization.
//      *
//      * @var array<int, string>
//      */
//     protected $hidden = [
//         'password',
//         'remember_token',
//     ];

//     /**
//      * The attributes that should be cast.
//      *
//      * @var array<string, string>
//      */
//     protected $casts = [
//         'email_verified_at' => 'datetime',
//     ];
    
//     /**
//  * Check if user is admin
//  */
// public function isAdmin()
// {
//     return $this->user_type === 'admin';
// }

// /**
//  * Check if user is vendor
//  */
// public function isVendor()
// {
//     return $this->user_type === 'vendor';
// }

// /**
//  * Get vendor profile
//  */
// public function vendor()
// {
//     return $this->belongsTo(Vendor::class);
// }

// // Add to User model

// public function role()
// {
//     return $this->belongsTo(Role::class);
// }

// public function hasMenuAccess($menu)
// {
//     if (!$this->role) return false;
//     return $this->role->hasMenuAccess($menu);
// }
// }
