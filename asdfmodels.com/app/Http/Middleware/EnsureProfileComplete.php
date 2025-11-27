<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileComplete
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return $next($request);
        }

        // Skip for admin users
        if ($user->is_admin) {
            return $next($request);
        }

        // Skip if already on profile edit page or verification pages
        if ($request->routeIs('profile.model.edit') || 
            $request->routeIs('photographers.profile.edit') ||
            $request->routeIs('profile.model.update') ||
            $request->routeIs('photographers.profile.update') ||
            $request->routeIs('verification.*') ||
            $request->routeIs('logout') ||
            $request->routeIs('profile.edit') ||
            $request->routeIs('profile.update')) {
            return $next($request);
        }

        // Check if profile is complete
        if ($user->is_photographer) {
            if (!$user->photographerProfile) {
                return redirect()->route('photographers.profile.edit')
                    ->with('status', 'Please complete your photographer profile to continue.');
            }
        } else {
            if (!$user->modelProfile) {
                return redirect()->route('profile.model.edit')
                    ->with('status', 'Please complete your model profile to continue.');
            }
        }

        return $next($request);
    }
}

