<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  string  ...$roles  Role slugs (e.g. super_admin, doctor, staff)
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        $userRoleSlug = $request->user()->role?->slug;

        if (! $userRoleSlug || ! in_array($userRoleSlug, $roles)) {
            abort(403, 'Accès non autorisé.');
        }

        return $next($request);
    }
}
