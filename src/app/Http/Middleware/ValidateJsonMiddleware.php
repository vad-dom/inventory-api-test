<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use JsonException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ValidateJsonMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $content = trim($request->getContent());

        if (! $request->isJson() || $content === '') {
            return $next($request);
        }

        try {
            json_decode(
                $content,
                true,
                flags: JSON_THROW_ON_ERROR
            );
        } catch (JsonException $e) {
            throw new BadRequestHttpException('Request body contains invalid JSON.', $e);
        }

        return $next($request);
    }
}
