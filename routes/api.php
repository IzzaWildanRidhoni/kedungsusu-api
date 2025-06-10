<?php

// Laravel 10 API - Kedung Susu Project (routes/api.php)

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\UserController;

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('/products', [ProductController::class, 'index']);

// Protected routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Admin only
    Route::middleware('role:admin')->group(function () {
        Route::apiResource('/users', UserController::class);
        Route::apiResource('/products', ProductController::class)->except(['index']);
    });
});
