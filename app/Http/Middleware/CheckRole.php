<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();
        
        if (!$user || $user->role !== $role) {
            return response()->json([
                'message' => 'Unauthorized: Insufficient permissions',
                'required_role' => $role,
                'user_role' => $user?->role ?? 'guest'
            ], 403);
        }

        return $next($request);
    }
}