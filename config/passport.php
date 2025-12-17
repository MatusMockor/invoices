<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Passport Guard
    |--------------------------------------------------------------------------
    |
    | Here you may specify which authentication guard Passport will use when
    | authenticating users. This value should correspond with one of your
    | guards that is already present in your "auth" configuration file.
    |
    */

    'guard' => 'web',

    /*
    |--------------------------------------------------------------------------
    | Encryption Keys
    |--------------------------------------------------------------------------
    |
    | Passport uses encryption keys while generating secure access tokens for
    | your application. By default, the keys are stored as local files but
    | can be set via environment variables when that is more convenient.
    |
    */

    'private_key' => env('PASSPORT_PRIVATE_KEY'),

    'public_key' => env('PASSPORT_PUBLIC_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Passport Database Connection
    |--------------------------------------------------------------------------
    |
    | By default, Passport's models will utilize your application's default
    | database connection. If you wish to use a different connection you
    | may specify the configured name of the database connection here.
    |
    */

    'connection' => env('PASSPORT_CONNECTION'),

    /*
    |--------------------------------------------------------------------------
    | Token Expiration Settings
    |--------------------------------------------------------------------------
    |
    | Configure token expiration times. These values can be overridden via
    | environment variables for different environments.
    |
    | PRD Requirements:
    | - Access token: 1 hour (60 minutes)
    | - Refresh token: 30 days
    |
    */

    'access_token_expire_minutes' => env('PASSPORT_ACCESS_TOKEN_EXPIRE_MINUTES', 60),

    'refresh_token_expire_days' => env('PASSPORT_REFRESH_TOKEN_EXPIRE_DAYS', 30),

];
