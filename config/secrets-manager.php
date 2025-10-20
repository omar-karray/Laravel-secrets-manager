<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Secrets Driver
    |--------------------------------------------------------------------------
    |
    | This option controls which secrets backend should be used when no driver
    | is specified explicitly. Supported drivers are defined in the "drivers"
    | section below.
    |
    */

    'default' => env('SECRETS_MANAGER_DRIVER', 'vault'),

    /*
    |--------------------------------------------------------------------------
    | Secrets Bootstrap
    |--------------------------------------------------------------------------
    |
    | Configure how environment variables should be hydrated from the secrets
    | backend during application boot. Each entry should map an environment
    | variable to a path understood by the configured driver.
    |
    | Example:
    | 'paths' => [
    |     'APP_KEY' => 'apps/laravel#app_key',
    |     'DB_PASSWORD' => 'apps/laravel/database#password',
    | ],
    |
    */

    'bootstrap' => [
        'enabled' => env('SECRETS_MANAGER_BOOTSTRAP', false),
        'paths' => [],
        'driver' => env('SECRETS_MANAGER_BOOTSTRAP_DRIVER'),
        'fail_on_missing' => env('SECRETS_MANAGER_FAIL_ON_MISSING', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Secrets Cache
    |--------------------------------------------------------------------------
    |
    | Optionally cache resolved secrets to reduce backend traffic. When enabled,
    | the package will use the configured cache store for the provided TTL.
    |
    */

    'cache' => [
        'enabled' => env('SECRETS_MANAGER_CACHE', false),
        'store' => env('SECRETS_MANAGER_CACHE_STORE'),
        'ttl' => env('SECRETS_MANAGER_CACHE_TTL', 300),
    ],

    /*
    |--------------------------------------------------------------------------
    | Driver Configurations
    |--------------------------------------------------------------------------
    |
    | Define configuration for each secrets backend driver. A driver may share
    | configuration keys with other drivers. You can add additional driver
    | entries to support custom integrations.
    |
    */

    'drivers' => [
        'vault' => [
            'address' => env('VAULT_ADDR', 'http://127.0.0.1:8200'),
            'token' => env('VAULT_TOKEN'),
            'namespace' => env('VAULT_NAMESPACE'),
            'verify' => env('VAULT_VERIFY', true),
            'timeout' => env('VAULT_TIMEOUT', 5),
            'engine' => [
                'mount' => env('VAULT_ENGINE_MOUNT', 'secret'),
                'version' => env('VAULT_ENGINE_VERSION', 2),
            ],
        ],

        'openbao' => [
            'address' => env('OPENBAO_ADDR', env('VAULT_ADDR', 'http://127.0.0.1:8200')),
            'token' => env('OPENBAO_TOKEN', env('VAULT_TOKEN')),
            'namespace' => env('OPENBAO_NAMESPACE'),
            'verify' => env('OPENBAO_VERIFY', env('VAULT_VERIFY', true)),
            'timeout' => env('OPENBAO_TIMEOUT', env('VAULT_TIMEOUT', 5)),
            'engine' => [
                'mount' => env('OPENBAO_ENGINE_MOUNT', env('VAULT_ENGINE_MOUNT', 'secret')),
                'version' => env('OPENBAO_ENGINE_VERSION', env('VAULT_ENGINE_VERSION', 2)),
            ],
        ],
    ],
];
