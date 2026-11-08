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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    // Flexiload (mobile recharge) through sms.iglweb.com: the key and PIN are kept in .env only, never in the code
    'flexiload' => [
        'api_key' => env('FLEXI_API_KEY'),
        'pin' => env('FLEXI_PIN'),
    ],

    // Attendance device API (testzk.iglweb.com) used by the HitApiAndSaveToDatabase command: key in .env only
    'zk_attendance' => [
        'api_key' => env('ZK_API_KEY'),
    ],

];
