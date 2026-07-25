<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API Rate Limits
    |--------------------------------------------------------------------------
    |
    | Defines the rate limit for the number of requests that can be
    | executed against the client and internal (application) APIs along with
    | certain other endpoints over a defined period (1 minute for most)
    */
    // NOTE (Velrix fork): the client/application API limiters are keyed by the
    // requesting user's uuid (see RouteServiceProvider), and the Velrix app brokers
    // EVERY end-user's server actions through a single admin key. So these buckets
    // are the whole platform's shared throughput, not one human clicking around —
    // the stock defaults (256/min client, 5/min websocket, etc.) throttle almost
    // immediately. Raised well above stock accordingly; Velrix also enforces its own
    // per-user limits before a request ever reaches here. Each value can still be
    // overridden via its env var. (Divergence from upstream — keep on merge.)
    'rate_limit' => [
        'client_period' => env('APP_API_CLIENT_RATELIMIT_PERIOD', 1),
        'client' => env('APP_API_CLIENT_RATELIMIT', 5000),

        'application_period' => env('APP_API_APPLICATION_RATELIMIT_PERIOD', 1),
        'application' => env('APP_API_APPLICATION_RATELIMIT', 2000),

        'password_reset_period' => env('APP_API_PASSWORD_RESET_RATELIMIT_PERIOD', 1),
        'password_reset' => env('APP_API_PASSWORD_RESET_RATELIMIT', 2),

        'websocket_period' => env('APP_API_WEBSOCKET_RATELIMIT_PERIOD', 1),
        'websocket' => env('APP_API_WEBSOCKET_RATELIMIT', 60),

        'backup_restore_period' => env('APP_API_BACKUP_RESTORE_RATELIMIT_PERIOD', 15),
        'backup_restore' => env('APP_API_BACKUP_RESTORE_RATELIMIT', 3),

        'database_create_period' => env('APP_API_DATABASE_CREATE_RATELIMIT_PERIOD', 1),
        'database_create' => env('APP_API_DATABASE_CREATE_RATELIMIT', 30),

        'subuser_create_period' => env('APP_API_SUBUSER_CREATE_RATELIMIT_PERIOD', 15),
        'subuser_create' => env('APP_API_SUBUSER_CREATE_RATELIMIT', 10),

        'file_pull_period' => env('APP_API_FILE_PULL_RATELIMIT_PERIOD', 10),
        'file_pull' => env('APP_API_FILE_PULL_RATELIMIT', 100),

        'default_period' => env('APP_API_DEFAULT_RATELIMIT_PERIOD', 1),
        'default' => env('APP_API_DEFAULT_RATELIMIT', 30),
    ],
];
