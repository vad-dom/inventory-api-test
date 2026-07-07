<?php

namespace App\Http\Middleware;

use App\Http\Responses\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-Api-Key');

        if (! hash_equals((string) config('api.key'), (string) $apiKey)) {
            return ApiResponse::error(
                code: 'UNAUTHORIZED',
                message: 'Invalid API key',
                status: 401
            );
        }

        return $next($request);
    }
}
