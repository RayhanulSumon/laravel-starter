<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    /**
     * Handle an incoming request and ensure the user has the 'super-admin' role.
     *
     * @param Request $request  The incoming HTTP request.
     * @param Closure $next  The next middleware or request handler.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user || $user->role !== UserRole::SUPER_ADMIN || !$request->user()->tokenCan('super-admin')) {
            return response()->json(['message' => 'Forbidden'], Response::HTTP_FORBIDDEN);
        }
        return $next($request);
    }
}
