<?php

namespace App\Exceptions;

use App\Http\Responses\ApiResponse;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ApiExceptionRenderer
{
    public static function register(Exceptions $exceptions): void
    {
        $isApiRequest = static function (Request $request): bool {
            return $request->is('api/*');
        };

        $exceptions->shouldRenderJsonWhen($isApiRequest);

        $exceptions->render(function (ValidationException $e, Request $request) use ($isApiRequest) {
            if (! $isApiRequest($request)) {
                return null;
            }

            return ApiResponse::error(
                code: 'VALIDATION_ERROR',
                message: 'Validation failed',
                status: 422,
                fields: $e->errors(),
            );
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) use ($isApiRequest) {
            if (! $isApiRequest($request)) {
                return null;
            }

            return ApiResponse::error(
                code: 'NOT_FOUND',
                message: 'Resource not found',
                status: 404,
            );
        });
    }
}
