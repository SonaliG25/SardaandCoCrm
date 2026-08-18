<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = ['name', 'description', 'menu_access', 'is_system'];
    
    protected $casts = [
        'menu_access' => 'array',
        'is_system' => 'boolean',
    ];

    // Relationship
    public function users()
    {
        return $this->hasMany(User::class);
    }

    // Check if role has menu access
    public function hasMenuAccess($menu)
    {
        return $this->menu_access[$menu] ?? false;
    }

    // Get all accessible menus
    public function getAccessibleMenus()
    {
        return array_keys(array_filter($this->menu_access, fn($v) => $v === true));
    }
}