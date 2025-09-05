<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

// Route::prefix('auth')->group(function() {
//     Route::post('login', [AuthController::class, 'login']);
//     Route::middleware('auth:api')->group(function() {
//         Route::get('me',[ AuthController::class, 'me']);
//         Route::post('logout', [AuthController::class, 'logout']);
//     });
// });

Route::middleware('throttle:login')->post('/auth/login', [AuthController::class, 'login']);

// Protected auth routes
Route::middleware('auth:api')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
});


Route::get('/products', [ProductController::class, 'fetch']);
Route::get('/products/by-inn/{inn}', [ProductController::class, 'fetchByInn']);
// Route::post('/products/upload', [ProductController::class, 'upload']);


Route::post('/products/upload', [ProductController::class, 'upload'])->middleware('auth:api');

