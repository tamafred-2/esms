<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (Auth::user()->usertype !== 'staff') {
            return redirect()->back()->with('error', 'Unauthorized access. Staff only.');
        }

        return $next($request);
    }
}
