<?php

use App\Http\Controllers\Api\ApiProductController;
use App\Http\Controllers\Api\ApiSaleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| These routes are for the REST API. They require authentication
| via Sanctum or Bearer token.
|
*/

Route::middleware(['auth:sanctum'])->group(function() {
    
    // Products API
    Route::get('/products', [ApiProductController::class, 'index']);
    Route::get('/products/search', [ApiProductController::class, 'search']);
    Route::get('/products/low-stock', [ApiProductController::class, 'lowStock']);
    Route::get('/products/{product}', [ApiProductController::class, 'show']);
    Route::post('/products', [ApiProductController::class, 'store']);
    Route::put('/products/{product}', [ApiProductController::class, 'update']);
    Route::delete('/products/{product}', [ApiProductController::class, 'destroy']);
    Route::post('/products/{product}/stock', [ApiProductController::class, 'updateStock']);
    
    // Sales API
    Route::get('/sales', [ApiSaleController::class, 'index']);
    Route::get('/sales/summary', [ApiSaleController::class, 'summary']);
    Route::get('/sales/top-products', [ApiSaleController::class, 'topProducts']);
    Route::get('/sales/{sale}', [ApiSaleController::class, 'show']);
    Route::post('/sales', [ApiSaleController::class, 'store']);
});