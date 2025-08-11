<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/products', [ProductController::class, 'fetch']);
Route::post('/products/upload', [ProductController::class, 'upload']);
Route::get('/products/by-name/{name}', [ProductController::class, 'fetchByName']);
Route::get('/products/by-inn/{inn}', [ProductController::class, 'fetchByInn']);

