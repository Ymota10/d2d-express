<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ShopifyController;
use App\Http\Controllers\Api\WooOrderWebhookController;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

// // Define the "api" rate limiter
// RateLimiter::for('api', function ($request) {
//     return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
// });

// Public login route
Route::post('/login', [AuthController::class, 'login']);

// Authenticated API routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/orders', [OrderController::class, 'index']);
});

// Find Shop API
Route::post('/shopify/find-shop', [ShopifyController::class, 'findShop']);

// -----------------------------
// WooCommerce Integration
// -----------------------------

// Public webhook route for WooCommerce (no auth)
Route::post(
    '/webhooks/woocommerce/orders',
    [WooOrderWebhookController::class, 'store']
);
// ->middleware('verify.woo'); // optional, enable after testing

// NEWWWWWWWWWWW

// Route::middleware('api.key')->group(function () {

//     Route::get('/orders', [OrderApiController::class, 'index']);

//     Route::get('/orders/{id}', [OrderApiController::class, 'show']);

//     Route::post('/orders', [OrderApiController::class, 'store']);

//     Route::put('/orders/{id}/status', [OrderApiController::class, 'updateStatus']);

// });
