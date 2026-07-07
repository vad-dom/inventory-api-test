<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class ApiResponse
{
    public static function success(
        mixed $data = null,
        array $meta = [],
        int $status = 200
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => self::meta($meta),
        ], $status);
    }

    public static function error(
        string $code,
        string $message,
        int $status,
        array $fields = [],
        array $meta = []
    ): JsonResponse {
        $error = [
            'code' => $code,
            'message' => $message,
        ];

        if ($fields !== []) {
            $error['fields'] = $fields;
        }

        return response()->json([
            'success' => false,
            'error' => $error,
            'meta' => self::meta($meta),
        ], $status);
    }

    private static function meta(array $meta = []): array
    {
        return array_merge($meta, [
            'request_id' => (string) Str::uuid(),
        ]);
    }
}
