<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Support pipe-separated roles or multiple parameters
        $allowedRoles = [];
        foreach ($roles as $roleGroup) {
            foreach (explode('|', $roleGroup) as $r) {
                $allowedRoles[] = trim($r);
            }
        }

        // System Admin always has full access
        if ($user->role_enum === 'admin' || $user->hasRole('admin')) {
            return $next($request);
        }

        if ($user->hasRoleOrEnum($allowedRoles)) {
            return $next($request);
        }

        abort(403, 'Unauthorized role for this module.');
    }
}
