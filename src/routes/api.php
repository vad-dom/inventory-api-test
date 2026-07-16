<?php

use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\StatisticsController;
use App\Http\Controllers\Api\V1\StockController;
use App\Http\Controllers\Api\V1\WarehouseController;
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

        Route::post('warehouses', [WarehouseController::class, 'store']);
        Route::get('warehouses', [WarehouseController::class, 'index']);
        Route::get('warehouses/{id}', [WarehouseController::class, 'show']);
        Route::put('warehouses/{id}', [WarehouseController::class, 'update']);
        Route::patch('warehouses/{id}/deactivate', [WarehouseController::class, 'deactivate']);
        Route::delete('warehouses/{id}', [WarehouseController::class, 'destroy']);

        Route::post('stock/income', [StockController::class, 'income']);
        Route::post('stock/write-off', [StockController::class, 'writeOff']);
        Route::post('stock/transfer', [StockController::class, 'transfer']);

        Route::get('stock/balances', [StockController::class, 'balances']);
        Route::get('stock/balances/{product}/{warehouse}', [StockController::class, 'balance']);
        Route::get('stock/movements', [StockController::class, 'movements']);
        Route::get('statistics/stock', [StatisticsController::class, 'stock']);
    });
