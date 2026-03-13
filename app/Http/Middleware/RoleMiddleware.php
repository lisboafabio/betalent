<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $userRole = $request->user()?->role;
        $userRoleStr = $userRole instanceof \BackedEnum ? $userRole->value : $userRole;

        if (!$userRoleStr || !in_array($userRoleStr, $roles)) {
            return response()->json(['message' => 'Unauthorized. Insufficient privileges.'], 403);
        }

        return $next($request);
    }
}
