<?php

namespace App\Exceptions;

use App\Http\Responses\ApiResponse;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class ApiExceptionRenderer
{
    public static function register(Exceptions $exceptions): void
    {
        $isApiRequest = static function (Request $request): bool {
            return $request->is('api/*');
        };

        $exceptions->shouldRenderJsonWhen($isApiRequest);

        $exceptions->render(function (Throwable $e, Request $request) use ($isApiRequest) {
            if (! $isApiRequest($request)) {
                return null;
            }

            return match (true) {
                $e instanceof ValidationException => ApiResponse::error(
                    code: 'VALIDATION_ERROR',
                    message: 'Validation failed.',
                    status: Response::HTTP_UNPROCESSABLE_ENTITY,
                    fields: $e->errors(),
                ),

                $e instanceof NotFoundHttpException => ApiResponse::error(
                    code: 'NOT_FOUND',
                    message: 'Resource not found.',
                    status: Response::HTTP_NOT_FOUND,
                ),

                $e instanceof ApiBusinessException => ApiResponse::error(
                    code: $e->getErrorCode(),
                    message: $e->getMessage(),
                    status: $e->getStatus(),
                ),

                default => null,
            };
        });
    }
}
