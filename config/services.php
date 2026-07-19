<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'woocommerce' => [
        'store_url' => env('WOOCOMMERCE_STORE_URL'),           // your Woo site URL
        'consumer_key' => env('WOOCOMMERCE_CONSUMER_KEY'),    // from .env
        'consumer_secret' => env('WOOCOMMERCE_CONSUMER_SECRET'), // from .env
        'webhook_secret' => env('WOOCOMMERCE_WEBHOOK_SECRET'),   // from .env
    ],
    'bosta' => [
        'base_url' => env('BOSTA_BASE_URL', 'https://business.bosta.co/api/v2'),
        'api_key' => env('BOSTA_API_KEY'),
    ],

    'shopify_internal' => [
        'url' => env('SHOPIFY_INTERNAL_API_URL'),
        'key' => env('SHOPIFY_INTERNAL_API_KEY'),
    ],

];
