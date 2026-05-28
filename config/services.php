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

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'cognifit' => [
        'client_id' => env('COGNIFIT_CLIENT_ID'),
        'client_secret' => env('COGNIFIT_CLIENT_SECRET'),
        'hash' => env('COGNIFIT_HASH'),
        'launch_url' => env('COGNIFIT_LAUNCH_URL'),
        'base_url' => env('COGNIFIT_API_BASE_URL', 'https://api.cognifit.com'),
    ],

    'firebase_web' => [
        'apiKey' => env('FIREBASE_WEB_API_KEY'),
        'authDomain' => env('FIREBASE_WEB_AUTH_DOMAIN'),
        'databaseURL' => env('FIREBASE_WEB_DATABASE_URL'),
        'projectId' => env('FIREBASE_WEB_PROJECT_ID'),
        'storageBucket' => env('FIREBASE_WEB_STORAGE_BUCKET'),
        'messagingSenderId' => env('FIREBASE_WEB_MESSAGING_SENDER_ID'),
        'appId' => env('FIREBASE_WEB_APP_ID'),
        'measurementId' => env('FIREBASE_WEB_MEASUREMENT_ID'),
    ],

];
