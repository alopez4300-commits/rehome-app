<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOnlyAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated and is admin
        if (auth()->check() && auth()->user()->isAdmin()) {
            return $next($request);
        }

        // If user is authenticated but not admin, logout and redirect to login
        if (auth()->check()) {
            auth()->logout();
            session()->invalidate();
            session()->regenerateToken();
            
            return redirect()->route('filament.admin.auth.login')
                ->withErrors(['email' => 'Only administrators can access this panel.']);
        }

        // If not authenticated, continue to auth middleware
        return redirect()->route('filament.admin.auth.login');
    }
}
