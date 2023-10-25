<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class CheckRole
{
    public function handle($request, Closure $next, ...$roles)
    {
        $role = JWTAuth::parseToken()->getPayload()->get('role');
        if ($role) {
            if (in_array($role, $roles)) {
                return $next($request);
            }
        }
        return response(['message' => 'Unauthorized'], 403);
    }
}
