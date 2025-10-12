<?php

use App\Http\Controllers\API\WooCommerceWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/woocommerce/orders', [WooCommerceWebhookController::class, 'store']);
