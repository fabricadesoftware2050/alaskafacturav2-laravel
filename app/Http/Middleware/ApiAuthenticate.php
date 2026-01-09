<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;

class ApiAuthenticate
{
    public function handle($request, Closure $next, ...$guards)
    {
        try {
            // Si falla, lanzará AuthenticationException
            Auth::shouldUse($guards[0] ?? 'api');

            if (Auth::guard($guards[0] ?? 'api')->check() === false) {
                throw new AuthenticationException();
            }

        } catch (AuthenticationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'No autorizado'
            ], 401);
        }

        return $next($request);
    }
}
