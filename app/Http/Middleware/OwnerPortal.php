<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class OwnerPortal
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Unauthenticated.'], 401)
                : redirect()->route('login');
        }

        if (!Auth::user()->hasRole('owner')) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Forbidden: Owner portal access only.'], 403);
            }
            
            return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki akses ke Portal Owner.');
        }

        return $next($request);
    }
}
