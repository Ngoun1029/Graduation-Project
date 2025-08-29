<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string[]  ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = Auth::user();

        // Check if user exists and has a role
        if (!$user || !$user->role || !in_array($user->role->name, $roles)) {
            return response()->json([
                'verified' => false,
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        return $next($request);
    }
}
