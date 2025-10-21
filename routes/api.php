<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\CouponController;

Route::get('/', function () {
    return response()->json(['message' => 'API is working']);
});


// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');

// Public product & category routes
Route::get('/products',       [ProductController::class, 'index']);
Route::get('/products/{id}',  [ProductController::class, 'show']);
Route::get('/categories',     [CategoryController::class, 'index']);
Route::get('/banners',        [BannerController::class, 'index']);
Route::get('/coupons',        [CouponController::class, 'index']);

// Protected user routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::put('/update-profile', [AuthController::class, 'updateProfile']);

    // Orders
    Route::get('/orders/my-orders', [OrderController::class, 'myOrders']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::put('/orders/{id}/cancel', [OrderController::class, 'cancelOrder']);

    // Reviews & Comments
    Route::apiResource('reviews', ReviewController::class)->only(['store', 'update', 'destroy']);
    Route::apiResource('comments', CommentController::class)->only(['store', 'update', 'destroy']);
});

// Admin routes
Route::middleware(['auth:sanctum', 'can:isAdmin'])->prefix('admin')->group(function () {
    // Users
    Route::apiResource('users', UserController::class);
    
    // Products - REMOVED DUPLICATE
    Route::get('/products', [ProductController::class, 'index']); 
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);
    
    // Categories
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

    // Orders
    Route::get('/orders', [OrderController::class, 'index']);
    Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus']);
    Route::delete('/orders/{id}', [OrderController::class, 'destroy']);
});