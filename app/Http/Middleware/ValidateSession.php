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
            
            // Skip check if the user just logged in via Remember Me
            // because the new session hasn't been written to the database yet.
            if (Auth::viaRemember()) {
                return $next($request);
            }
            
            // Check if current session still exists in database for this user
            $sessionExists = DB::table('sessions')
                ->where('id', $currentSessionId)
                ->where('user_id', $user->id)
                ->exists();
            
            if (!$sessionExists) {
                // Session was invalidated (e.g. by too many concurrent logins)
                Auth::guard('web')->logout();
                
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                // Redirect to login with message
                return redirect()->route('login')->with('error', 'You have been logged out because your session was invalidated by a new login.');
            }
        }
        
        return $next($request);
    }
}
