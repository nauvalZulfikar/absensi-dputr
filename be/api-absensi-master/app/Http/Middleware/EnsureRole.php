<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Route guard: caller must hold at least one of the given role names
 * (checked against role_has_users -> roles.name). Returns a clean JSON
 * 401 when unauthenticated and 403 when the role is missing.
 *
 * Usage: ->middleware(['auth:api', 'role:admin,user_admin'])
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(
                ['meta' => ['status' => false, 'message' => 'Unauthenticated.', 'code' => 401]],
                401
            );
        }

        $hasRole = $user->roles()
            ->whereHas('role', fn ($q) => $q->whereIn('name', $roles))
            ->exists();

        if (!$hasRole) {
            return response()->json(
                ['meta' => ['status' => false, 'message' => 'Forbidden: role tidak mencukupi.', 'code' => 403]],
                403
            );
        }

        return $next($request);
    }
}
