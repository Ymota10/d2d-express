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
        'url' => env('https://kuddl-eg.com'),
        'consumer_key' => env('ck_7e7a9014dddfad0270a2ebc8159dfd23fdba0416'),
        'consumer_secret' => env('cs_6b31900e26880150882cf2bc1fe40dfe409d5c4b'),
    ],
    'bosta' => [
        'base_url' => env('BOSTA_BASE_URL', 'https://business.bosta.co/api/v2'),
        'api_key' => env('BOSTA_API_KEY'),
    ],

];
