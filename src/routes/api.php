<?php

use App\Http\Controllers\Api\V1\ProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->middleware('api.key')
    ->group(function (): void {
        Route::post('products', [ProductController::class, 'store']);
        Route::get('products', [ProductController::class, 'index']);
        Route::get('products/{id}', [ProductController::class, 'show']);
        Route::put('products/{id}', [ProductController::class, 'update']);
        Route::patch('products/{id}/deactivate', [ProductController::class, 'deactivate']);
        Route::delete('products/{id}', [ProductController::class, 'destroy']);
    });
