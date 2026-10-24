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

    // Image storage for the website. The secret key is normally entered on the Settings screen.
    'supabase' => [
        'url' => env('SUPABASE_URL', 'https://pktnqerzatrdndhbtsqp.supabase.co'),
        'secret_key' => env('SUPABASE_SECRET_KEY'),
    ],

    // Vercel Cron sends this as a bearer token to /hooks/daily when it is set.
    'cron_secret' => env('CRON_SECRET'),
    // Deploy hook URL that rebuilds the static website (Vercel deploy hook, or a build script on other hosts).
    'site_deploy_hook' => env('SITE_DEPLOY_HOOK'),
    // A GitHub token when the hook is GitHub's repository dispatch address (Hostinger builds).
    'site_deploy_token' => env('SITE_DEPLOY_TOKEN'),
    // The website folder when the admin runs on the same server (Hostinger), for instant publishing.
    'site_public_path' => env('SITE_PUBLIC_PATH'),

];
