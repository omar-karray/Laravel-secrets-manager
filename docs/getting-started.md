# Getting Started

This guide walks you through installing Laravel Vault Suite, configuring your first backend, and validating the setup with the built-in commands.

## Prerequisites

- Laravel 10 or higher running on PHP 8.2+
- Access to a Vault/OpenBao cluster (local dev or remote)
- CLI access to your Laravel project for running Artisan commands

## 1. Install the package

```bash
composer require deepdigs/laravel-vault-suite
```

For local development with the Git checkout, add a path repository entry:

```json
{
    "repositories": [
        { "type": "path", "url": "../laravel-vault-suite" }
    ]
}
```

Then require the `*@dev` constraint to symlink the local package.

## 2. Publish configuration

```bash
php artisan vendor:publish --tag="vault-suite-config"
```

This creates `config/vault-suite.php`. Update the environment variables referenced inside the file.

```dotenv
VAULT_SUITE_DRIVER=vault
VAULT_ADDR=http://127.0.0.1:8200
VAULT_TOKEN=root-or-approle-token
VAULT_ENGINE_MOUNT=secret
VAULT_ENGINE_VERSION=2
```

See the [Configuration Reference](configuration.md) for every available setting.

## 3. Verify connectivity

Run a simple status check:

```bash
php artisan vault:enable-engine secret/test --option=version=2
```

If the command succeeds, the suite is talking to your backend. Follow up with:

```bash
php artisan vault:unseal key1 key2 key3
```

(using dummy keys against a dev server or real keys in production).

## 4. Programmatic access

Inject the service or use the facade once you need secrets inside your codebase:

```php
use Deepdigs\LaravelVaultSuite\Facades\LaravelVaultSuite;

$config = LaravelVaultSuite::fetch('apps/laravel/database');
```

More examples live in the [Programmatic API](api.md) guide.

## Upgrading

When new versions are tagged:

```bash
composer update deepdigs/laravel-vault-suite
```

Review the [Changelog](changelog.md) for migration notes.

## Troubleshooting

| Symptom | Suggested fix |
| --- | --- |
| `Class "Memcached" not found` during composer scripts | Set `CACHE_STORE=file` (or install the PHP Memcached extension) when running composer commands. |
| `HTTP 403` / `permission denied` | Ensure the token used in `VAULT_TOKEN` has policy access to the secret path you are working with. |
| Artisan command cannot find your driver | Confirm the driver name exists under `drivers` in `config/vault-suite.php` and you published the config. |

Still stuck? Open a discussion or issue on GitHub—links are in the docs sidebar.
