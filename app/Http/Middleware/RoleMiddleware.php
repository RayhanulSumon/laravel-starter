<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request and ensure the user has one of the allowed roles.
     *
     * @param Request $request  The incoming HTTP request.
     * @param Closure $next  The next middleware or request handler.
     * @param  string  ...$roles  Allowed roles for this route (e.g., 'admin', 'super-admin').
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();
        // Convert all string roles to UserRole enum for comparison
        $allowedRoles = array_map(fn($role) => UserRole::tryFrom($role), $roles);
        $tokenAbilities = array_map(fn($role) => $role ? $role->value : null, $allowedRoles);
        if (
            !$user ||
            !in_array($user->role, $allowedRoles, true) ||
            !$request->user()->tokenCan($user->role->value) ||
            !in_array(true, array_map(fn($ability) => $request->user()->tokenCan($ability), $tokenAbilities), true)
        ) {
            return response()->json(['message' => 'Forbidden'], Response::HTTP_FORBIDDEN);
        }
        return $next($request);
    }
}
