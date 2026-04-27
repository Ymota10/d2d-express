<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FetchShopifyOrdersController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ShopifyController;
use App\Http\Controllers\Api\ShopifyWriteController;
use App\Http\Controllers\Api\UpdateShopifySettingsController;
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

// Link Shop API
Route::post('/shopify/link-shop', [ShopifyWriteController::class, 'linkShop']);

// Update Shopify Settings API
Route::post('/shopify/update-settings', [UpdateShopifySettingsController::class, 'update']);

// Fetch Shopify Orders API
Route::post('/shopify/fetch-orders', [FetchShopifyOrdersController::class, 'fetch']);

// Sync Orders API
Route::get('/shopify/sync-orders', [SyncOrdersController::class, 'sync']);

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
