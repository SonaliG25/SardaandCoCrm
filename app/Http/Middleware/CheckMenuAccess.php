<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckMenuAccess 
{
    public function handle(Request $request, Closure $next, $menu)
    {
        $user = auth()->user();

        if (!$user) {
            return redirect('/login');
        }

        // Super Admin always has access
        if ($user->role?->is_system) {
            return $next($request);
        }

        // Check if user's role has access to this menu
        if (!$user->hasMenuAccess($menu)) {
            return redirect()->back()->with('error', 'You do not have access to this section.');
        }

        return $next($request);
    }
}