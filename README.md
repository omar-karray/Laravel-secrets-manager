# Laravel Vault Suite

[![CI](https://github.com/omar-karray/laravel-vault-suite/actions/workflows/run-tests.yml/badge.svg)](https://github.com/omar-karray/laravel-vault-suite/actions/workflows/run-tests.yml)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/deepdigs/laravel-vault-suite.svg?style=flat-square)](https://packagist.org/packages/deepdigs/laravel-vault-suite)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/omar-karray/laravel-vault-suite/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/omar-karray/laravel-vault-suite/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/omar-karray/laravel-vault-suite/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/omar-karray/laravel-vault-suite/actions?query=workflow%3A%22Fix+PHP+code+style+issues%22+branch%3Amain)

Laravel Vault Suite connects your Laravel applications to dedicated secrets backends such as HashiCorp Vault and OpenBao. It ships with an extensible driver system, an expressive facade, and artisan tooling so you can read, write, and manage secrets without copying values into `.env` files.

## Features

- Driver manager for Vault-compatible backends (Vault, OpenBao out of the box).
- Fluent API and facade to fetch, write, list, and delete secrets.
- Secrets bootstrapper blueprint for loading environment variables at runtime.
- Artisan commands to unseal Vault and enable secrets engines.
- Extensible configuration for additional drivers and advanced HTTP options.

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
