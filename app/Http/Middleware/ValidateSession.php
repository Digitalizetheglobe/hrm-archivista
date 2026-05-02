<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ValidateSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only validate for authenticated users
        if (Auth::check()) {
            $user = Auth::user();
            $currentSessionId = $request->session()->getId();
            
            // Check if current session matches the user's tracked session
            if ($user->current_session_id && $user->current_session_id !== $currentSessionId) {
                // Session mismatch - logout the user
                Auth::guard('web')->logout();
                
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                // Redirect to login with message
                return redirect()->route('login')->with('error', 'You have been logged out because you logged in from another device.');
            }
        }
        
        return $next($request);
    }
}
