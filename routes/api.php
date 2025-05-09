<?php

use App\Http\Controllers\StockMovementController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;

Route::prefix('test')->group(function () {
    Route::get('/warehouses', [WarehouseController::class, 'index']);
    Route::get('/products', [ProductController::class, 'indexWithStocks']);

    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::put('/orders/{order}', [OrderController::class, 'update']);
    Route::post('/orders/{order}/{status}', [OrderController::class, 'changeStatus']);
    Route::get('/stock-movements', [StockMovementController::class, 'index']);
});
