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

        if (!$user) {
            abort(403, 'Unauthorized');
        }

        $requiredRoles = collect($roles)->filter(fn($r) => !str_starts_with($r, '!'))->toArray();
        $excludedRoles = collect($roles)->filter(fn($r) => str_starts_with($r, '!'))->map(fn($r) => ltrim($r, '!'))->toArray();

        if ($requiredRoles && !$user->hasAnyRole($requiredRoles)) {
            abort(403, 'Unauthorized');
        }

        if ($excludedRoles && $user->hasAnyRole($excludedRoles)) {
            abort(403, 'Unauthorized');
        }


        return $next($request);
    }

}
