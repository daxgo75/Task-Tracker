<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Stateful Domains
    |--------------------------------------------------------------------------
    |
    | Requests from the following domains / hosts will receive stateful API
    | authentication cookies that are typically used for CSRF protection
    | when making API requests from JavaScript frameworks.
    |
    */

    'stateful' => explode(',', env(
        'SANCTUM_STATEFUL_DOMAINS',
        sprintf(
            '%s%s',
            'localhost,localhost:3000,localhost:8000,localhost:8080,127.0.0.1',
            env('APP_URL') ? ',' . parse_url(env('APP_URL'), PHP_URL_HOST) : ''
        )
    )),

    /*
    |--------------------------------------------------------------------------
    | Expiration Minutes
    |--------------------------------------------------------------------------
    |
    | This value controls the number of minutes until an issued token will be
    | considered expired. If this value is null, personal access tokens can
    | remain valid indefinitely. This won't tweak the lifetime of first-
    | party session cookies. You may change this as needed.
    |
    | Default: 1440 minutes (24 hours)
    | Common values:
    |   60 = 1 hour
    |   1440 = 24 hours
    |   10080 = 1 week
    |   525600 = 1 year
    |
    */

    'expiration' => env('SANCTUM_TOKEN_EXPIRATION', 1440),

    /*
    |--------------------------------------------------------------------------
    | Sanctum Middleware
    |--------------------------------------------------------------------------
    |
    | When authenticating your first-party SPA that is executing on the same
    | top-level domain as you application, Sanctum requires a middleware to
    | be registered in your HTTP application's middleware stack. You may
    | override these for applications executing in environments outside
    | of the first-party context.
    |
    */

    'middleware' => [
        'verify_csrf_token' => App\Http\Middleware\VerifyCsrfToken::class,
        'encrypt_cookies' => App\Http\Middleware\EncryptCookies::class,
    ],

];
