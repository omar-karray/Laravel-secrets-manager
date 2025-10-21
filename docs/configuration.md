# Configuration Reference

After publishing the config (`php artisan vendor:publish --tag="vault-suite-config"`) you will find `config/vault-suite.php`. This file drives how the suite talks to Vault/OpenBao and how secrets are hydrated into your application.

## Default driver

```php
'default' => env('VAULT_SUITE_DRIVER', 'vault'),
```
Controls which backend is used when a command or API call does not specify a driver. Valid values out of the box are `vault` and `openbao`.

## Bootstrap mapping

The `bootstrap` section lets you map environment variables to secret paths so you can hydrate configuration during application boot (bootstrapper implementation is on the roadmap).

```php
'bootstrap' => [
    'enabled' => env('VAULT_SUITE_BOOTSTRAP', false),
    'paths' => [
        // 'APP_KEY' => 'apps/laravel#app_key',
    ],
    'driver' => env('VAULT_SUITE_BOOTSTRAP_DRIVER'),
    'fail_on_missing' => env('VAULT_SUITE_FAIL_ON_MISSING', true),
],
```

- **enabled** – Turn runtime hydration on/off without modifying the config file.
- **paths** – Map env keys to `secret-path#key` entries understood by the configured driver.
- **driver** – Optional override if you want bootstrap to use a different backend than the default.
- **fail_on_missing** – When `true`, missing secrets throw an exception; when `false`, missing values are ignored.

## Caching

```php
'cache' => [
    'enabled' => env('VAULT_SUITE_CACHE', false),
    'store' => env('VAULT_SUITE_CACHE_STORE'),
    'ttl' => env('VAULT_SUITE_CACHE_TTL', 300),
],
```

Enable this once you wire in the forthcoming cache layer. It will reduce round-trips to Vault/OpenBao when reading frequently accessed secrets.

## Driver configuration

Each driver lives under `drivers`. The defaults work for local development and most production setups:

```php
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
```

### Environment variable reference

| Variable | Purpose | Recommended production value |
| --- | --- | --- |
| `VAULT_ADDR` / `OPENBAO_ADDR` | Cluster address | HTTPS URL managed by your infrastructure team |
| `VAULT_TOKEN` / `OPENBAO_TOKEN` | Token used for API calls | Short-lived app role or approle-derived token |
| `VAULT_NAMESPACE` | Enterprise Vault namespace (optional) | Namespace slug |
| `VAULT_VERIFY` | Whether to verify TLS certificates | `true` (set a CA bundle if using internal PKI) |
| `VAULT_ENGINE_MOUNT` | Mount path for the default secrets engine | `secret` (KV v2) |
| `VAULT_ENGINE_VERSION` | KV engine version (`1` or `2`) | `2` unless you intentionally use KV v1 |

### Extending drivers

To add a new backend:

1. Implement `Deepdigs\LaravelVaultSuite\Contracts\SecretsDriver`.
2. Register a custom driver using `VaultSuiteManager::extend()` in your service provider.
3. Add the driver’s configuration under `drivers` and reference it via the `--driver` flag or API call.

```php
LaravelVaultSuite::driver('my-backend')->read('path/to/secret');
```

## Sample `.env` block

```dotenv
VAULT_SUITE_DRIVER=vault
VAULT_ADDR=https://vault.example.com
VAULT_TOKEN=
VAULT_ENGINE_MOUNT=secret
VAULT_ENGINE_VERSION=2
VAULT_VERIFY=true

OPENBAO_ADDR=https://openbao.example.com
OPENBAO_TOKEN=
```

Keep tokens out of version control—manage them via your secret store or CI/CD environment management.
