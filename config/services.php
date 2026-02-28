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

    
    
    
    'reloadly' => [
        'client_id' => env('RELOADLY_CLIENT_ID'),
        'client_secret' => env('RELOADLY_CLIENT_SECRET'),
        'api_url' => env('RELOADLY_API_URL'),
        'auth_url' => env('RELOADLY_AUTH_URL'),
        'webhook_secret' => env('RELOADLY_WEBHOOK_SECRET'),
        
    ],

    'termii' => [
        'api_key' => env('TERMII_API_KEY'),
        'secret_key' => env('TERMII_SECRET_KEY'),
        'base_url' => env('TERMII_BASE_URL'),
    ],

    'payscribe' => [
        'secret' =>  env('PAYSCRIBE_SECRET'),
        'public' => env('PAYSCRIBE_PUBLIC'),
        'api_url' => env('PYSCRIBE_API_URL'),
    ],

    'wise' => [
        // 'secret' =>  env('PAYSCRIBE_SECRET'),
        'key' => env('WISE_API_KEY'),
        'api_url' => env('WISE_API_URL'),
    ],

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
        'token' => env('AWS_SESSION_TOKEN'),
    ],

    'sendinblue' => [
        'key' => env('SENDINBLUE_API_KEY')
    ],

    'sendgrid' => [
        'key' => env('SENDGRID_API_KEY')
    ],

    'mandrill' => [
        'key' => env('MAILCHIMP_API_KEY')
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URL'),
    ],
    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT_URL'),
    ],
    'github' => [
        'client_id' => env('GITHUB_CLIENT_ID'),
        'client_secret' => env('GITHUB_CLIENT_SECRET'),
        'redirect' => env('GITHUB_REDIRECT'),
    ],

    'twilio' => [
        'sid' => env('TWILIO_SID'),
        'token' => env('TWILIO_AUTH_TOKEN'),
        'number' => env('TWILIO_NUMBER'),
        'whatsapp_number' => env('TWILIO_WHATSAPP_NUMBER'),
    ],
];