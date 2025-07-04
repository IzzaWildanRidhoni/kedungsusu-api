<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\OrderController;

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);

// midtrans
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::post('/orders', [OrderController::class, 'store']);
});
Route::post('/midtrans/callback', [OrderController::class, 'callback']); // Public route for Midtrans callback

// Protected routes (requires login)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // --- User Management ---
    Route::middleware('permission:user.read')->get('/users', [UserController::class, 'index']);
    Route::middleware('permission:user.read')->get('/users/{user}', [UserController::class, 'show']);
    Route::middleware('permission:user.create')->post('/users', [UserController::class, 'store']);
    Route::middleware('permission:user.update')->put('/users/{user}', [UserController::class, 'update']);
    Route::middleware('permission:user.delete')->delete('/users/{user}', [UserController::class, 'destroy']);

    // --- Product Management ---
    Route::middleware('permission:product.create')->post('/products', [ProductController::class, 'store']);
    Route::middleware('permission:product.update')->put('/products/{product}', [ProductController::class, 'update']);
    Route::middleware('permission:product.delete')->delete('/products/{product}', [ProductController::class, 'destroy']);
});
