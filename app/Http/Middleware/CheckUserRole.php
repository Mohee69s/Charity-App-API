<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, \Closure $next, ...$roles)
    {
        $user = auth()->user();

        if (!$user || !$user->hasAnyRole($roles)) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }

}
