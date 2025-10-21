# RawDigs Core App Integration

The RawDigs core application can consume this package during development via a Composer path repository for fast iteration.

## 1. Configure Composer

Inside `rawdigs-core-app/composer.json` add (or extend) the repositories section:

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

Then require the package with a dev constraint:

```bash
cd rawdigs-core-app
composer require deepdigs/laravel-vault-suite:*@dev
```

## 2. Publish configuration

Publish the config file into the application:

```bash
php artisan vendor:publish --tag="vault-suite-config"
```

Edit `config/vault-suite.php` to fit RawDigs’ Vault/OpenBao environment. Existing bootstrap scripts like `scripts/vault-init.sh` can remain for initial setup until the artisan equivalents are fully adopted.

## 3. Set environment variables

Update `.env` or your secrets manager of choice:

```dotenv
VAULT_ADDR=https://vault.rawdigs.local
VAULT_TOKEN=<integration-token>
VAULT_NAMESPACE=rawdigs
VAULT_ENGINE_MOUNT=secret
```

## 4. Use the artisan tooling

- `php artisan vault:unseal --file=storage/keys/unseal.txt --reset`
- `php artisan vault:enable-engine secret/rawdigs --option=version=2`

These commands replace bespoke shell scripts and keep Vault operations inside Laravel.

## 5. Keep dependencies in sync

Whenever you update this package’s autoload or dependencies, rerun:

```bash
composer update deepdigs/laravel-vault-suite
```

This ensures the path repository delivers the latest classes to RawDigs without committing vendor changes.
