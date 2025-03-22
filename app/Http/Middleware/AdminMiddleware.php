<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Check if the authenticated user is an admin
        if (Auth::user()->usertype !== User::ROLE_ADMIN) {
            abort(403, 'Unauthorized action. You must be an administrator to access this area.');
        }

        // Update last active timestamp using a direct database update
        // This avoids potential model saving issues
        DB::table('users')
            ->where('id', Auth::id())
            ->update(['last_active_at' => now()]);

        return $next($request);
    }
}