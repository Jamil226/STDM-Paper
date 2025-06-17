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

    'device_registration' => [
        'secret_key' => env('DEVICE_REGISTRATION_SECRET'),
        'timestamp_tolerance_seconds' => 300, // 5 minutes tolerance for T1
    ],

    'device_registration' => [
        'session_token_expiry_seconds' => env('DEVICE_SESSION_TOKEN_EXPIRY', 3600), // Default 1 hour
        'timestamp_tolerance_seconds' => env('DEVICE_TIMESTAMP_TOLERANCE', 300),   // Default 5 minutes
    ],


];
