# Getting Started

## Installation

```bash
composer require deepdigs/laravel-vault-suite
```

Publish the configuration file to tailor driver settings and bootstrap behaviour:

```bash
php artisan vendor:publish --tag="vault-suite-config"
```

Update your environment variables (`.env`, secret manager, CI variables, etc.):

```dotenv
VAULT_SUITE_DRIVER=vault
VAULT_ADDR=http://127.0.0.1:8200
VAULT_TOKEN=app-token-or-root
VAULT_ENGINE_MOUNT=secret
VAULT_ENGINE_VERSION=2
```

The configuration file at `config/vault-suite.php` exposes additional options for OpenBao, caching, and the upcoming bootstrapper.

## Local development (path repository)

1. Clone this package alongside your Laravel application (the multi-root workspace approach works well).
2. Add a Composer path repository in your application’s `composer.json`:

    ```json
    {
        "repositories": [
            {
                "type": "path",
                "url": "../laravel-vault-suite"
            }
        ]
    }
    ```

3. Require the package using a dev constraint:

    ```bash
    composer require deepdigs/laravel-vault-suite:*@dev
    ```

4. After changing this package’s `composer.json` or autoload settings, run `composer update deepdigs/laravel-vault-suite` inside your app so the path repository picks up new classes.

## Configuration options

- `default` – Choose the default driver (`vault` or `openbao`).
- `bootstrap.paths` – Map environment variables to secret paths (bootstrapper implementation is planned).
- `cache` – Enable caching to reduce backend load (disabled by default).
- `drivers` – Configure Vault/OpenBao endpoints, tokens, namespaces, TLS options, and KV engine versions.

See `config/vault-suite.php` for inline documentation on each setting.

## Testing

Install package dependencies and run the Pest suite:

```bash
composer install
composer test
```

Enable the supplied HTTP fakes or your own Vault dev environment for end-to-end testing.
