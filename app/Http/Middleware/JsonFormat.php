<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JsonFormat
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        json_decode($request->getContent());
        if (json_last_error() != JSON_ERROR_NONE) {
            return response(['message' => 'Blogas JSON formatas'], 400);
        }
        return $next($request);
    }
}
