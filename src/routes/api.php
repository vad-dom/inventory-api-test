<?php

use App\Http\Responses\ApiResponse;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->middleware('api.key')
    ->group(function (): void {
        Route::get('/test', function () {
            return ApiResponse::success([
                'message' => 'API is working',
            ]);
        });
    });
