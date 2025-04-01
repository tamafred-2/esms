<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // First check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Then check if user is staff
        if (Auth::user()->usertype !== 'staff') {
            Auth::logout();
            return redirect()->route('login')
                ->with('error', 'Unauthorized access. Staff privileges required.');
        }

        // If all checks pass, proceed
        return $next($request);
    }
}
