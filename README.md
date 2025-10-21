# Laravel Vault Suite

[![CI](https://github.com/omar-karray/laravel-vault-suite/actions/workflows/run-tests.yml/badge.svg)](https://github.com/omar-karray/laravel-vault-suite/actions/workflows/run-tests.yml)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/deepdigs/laravel-vault-suite.svg?style=flat-square)](https://packagist.org/packages/deepdigs/laravel-vault-suite)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/omar-karray/laravel-vault-suite/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/omar-karray/laravel-vault-suite/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/omar-karray/laravel-vault-suite/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/omar-karray/laravel-vault-suite/actions?query=workflow%3A%22Fix+PHP+code+style+issues%22+branch%3Amain)

Laravel Vault Suite connects your Laravel applications to dedicated secrets backends such as HashiCorp Vault and OpenBao. It ships with an extensible driver system, an expressive facade, and artisan tooling so you can read, write, and manage secrets without copying values into `.env` files.

📘 Documentation: https://omar-karray.github.io/laravel-vault-suite/

## Features

- **Command-first operations** – Ship-ready Artisan commands (`vault:unseal`, `vault:enable-engine`, …) for the tasks operators and developers run every day.
- **Fluent PHP API** – Fetch, write, list, and delete secrets through a clean service/facade when you need programmatic access.
- **Multi-backend driver manager** – Vault and OpenBao out of the box with an extensible contract for additional backends.
- **Configuration & bootstrap blueprint** – Centralise driver settings today and hydrate Laravel configuration at runtime as the bootstrapper lands.
- **Documentation site** – [Guides on GitHub Pages](https://omar-karray.github.io/laravel-vault-suite/) cover installation, commands, configuration, and the API surface.

## Installation

```bash
composer require deepdigs/laravel-vault-suite
```

Publish the configuration file to tailor drivers and bootstrap behaviour:

```bash
php artisan vendor:publish --tag="vault-suite-config"
```

Add the relevant environment variables in your `.env` file (or server configuration):

```dotenv
VAULT_SUITE_DRIVER=vault
VAULT_ADDR=http://127.0.0.1:8200
VAULT_TOKEN=your-root-or-app-token
VAULT_ENGINE_MOUNT=secret
VAULT_ENGINE_VERSION=2
```

## Usage

Read a secret as an array:

```php
use Deepdigs\LaravelVaultSuite\Facades\LaravelVaultSuite;

$database = LaravelVaultSuite::fetch('apps/laravel/database');
```

Read a specific key from the secret payload:

```php
$password = LaravelVaultSuite::fetch('apps/laravel/database', 'password');
```

Write or update a secret:

```php
LaravelVaultSuite::put('apps/laravel/database', [
    'username' => 'laravel',
    'password' => 'new-password',
]);
```

List secret keys beneath a path:

```php
$keys = LaravelVaultSuite::list('apps/laravel');
```

## Artisan commands

- `vault:unseal` – Submit key shards (from CLI or a file) and track progress until Vault is unsealed.
  ```bash
  php artisan vault:unseal --file=storage/keys/unseal.txt --reset
  ```
- `vault:enable-engine` – Mount and configure secrets engines with typed options.
  ```bash
  php artisan vault:enable-engine secret/apps --option=version=2 --local
  ```

See [docs/commands.md](docs/commands.md) for the full option reference.

## Local development

- Use a multi-root VS Code workspace that includes this package and your Laravel app.
- Register the package as a [Composer path repository](https://getcomposer.org/doc/05-repositories.md#path) for hot-linked development.
- Only run `composer update deepdigs/laravel-vault-suite` after changing this package’s `composer.json` or autoloading configuration.
- When tagging for production use, publish to Packagist and update your application to use the release tag instead of the path repository.

## Testing

```bash
composer test
```

## Documentation

Project docs are powered by MkDocs. Preview locally with:

```bash
pip install mkdocs mkdocs-material
mkdocs serve
```

The documentation source lives in `docs/` and can be deployed to GitHub Pages via `mkdocs gh-deploy --clean`.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Guide: using Vault Suite in development

1. **Install & publish config** (see Installation above). Populate `VAULT_ADDR`, `VAULT_TOKEN`, and mount settings in `.env` or your secret manager.
2. **Verify connectivity**
   ```bash
   php artisan vault:status
   php artisan vault:enable-engine secret/apps --option=version=2
   ```
3. **Load existing secrets or commit new ones**
   ```bash
   php artisan vault:read secret/apps/database --json
   ```
   Write new values from PHP:
   ```php
   use Deepdigs\LaravelVaultSuite\LaravelVaultSuite;

   app(LaravelVaultSuite::class)->put('secret/apps/database', [
       'username' => 'laravel',
       'password' => Str::random(32),
   ]);
   ```
4. **Script it** – combine commands in deployment pipelines (e.g. run `vault:list` to confirm a rotation, then fetch credentials for tests).

## Guide: loading configuration from Vault

Until the bootstrapper ships, load secrets in a service provider or dedicated config loader:

```php
use Deepdigs\LaravelVaultSuite\LaravelVaultSuite;

class VaultConfigServiceProvider extends ServiceProvider
{
    public function boot(LaravelVaultSuite $vault): void
    {
        if (! app()->environment('production')) {
            return;
        }

        $database = $vault->fetch('secret/apps/database');

        config([
            'database.connections.mysql.username' => $database['username'],
            'database.connections.mysql.password' => $database['password'],
        ]);
    }
}
```

> ℹ️ When the bootstrapper lands, you will be able to map these keys directly inside `config/vault-suite.php` and hydrate them during `config:cache`.

## Guide: securing database credentials with Vault

1. **Create/mount a KV engine** dedicated to database credentials:
   ```bash
   php artisan vault:enable-engine database/credentials --type=kv --option=version=2
   ```
2. **Store the credentials** from an operator machine or CI job:
   ```bash
   php artisan vault:read database/credentials/mysql-root --json   # verify
   ```
   Or programmatically via Laravel Vault Suite:
   ```php
   $vault->put('database/credentials/mysql-app', [
       'username' => 'app',
       'password' => Str::random(40),
   ]);
   ```
3. **Load credentials into Laravel** at runtime (see provider example above) or inject them into environment variables before `config:cache`.
4. **Rotate safely**: rotate the credential in Vault (`put` new password), then redeploy the application so it fetches the updated secret. Combine with Vault’s DB secrets engine if you want automated rotation.

### Deployment pattern

- Run `php artisan vault:status` during health checks.
- If Vault is sealed, run `vault:unseal` with the key shards available to your SRE team or automation.
- Re-run `config:cache` after updating configuration if you load secrets at boot.

### Tips

- Never check tokens or key shards into source control. Use your CI/CD secret store.
- Grant the Laravel application a limited token (e.g. via AppRole) scoped to the paths it needs.
- Combine the suite with Vault’s audit logging to track access.
