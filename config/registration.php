<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Email Whitelist Configuration
    |--------------------------------------------------------------------------
    |
    | This option controls whether email whitelist validation is enabled
    | for user registration. When enabled, only emails present in the
    | email_whitelist table will be allowed to register.
    |
    */

    'email_whitelist_enabled' => env('REGISTRATION_EMAIL_WHITELIST_ENABLED', false),

];
