# Architecture Overview

## Vision

`deepdigs/laravel-vault-suite` provides a unified way for Laravel applications to consume runtime secrets from dedicated backends such as HashiCorp Vault and OpenBao. The primary goals are:

- Replace `.env` secrets with centrally managed vault entries.
- Offer operational workflows (unseal, engine management) directly inside Artisan.
- Expose a simple contract so additional secret backends can be added without modifying application code.

## Core Components

- **Vault Suite (`LaravelVaultSuite`)** – High-level service/facade that resolves drivers, performs reads/writes, manages unseal workflows, and exposes convenience helpers to the rest of the application.
- **Driver Manager (`VaultSuiteManager`)** – Extends Laravel’s driver pattern to construct and cache driver instances based on configuration.
- **Vault Driver (`Drivers\Vault\VaultSecretsDriver`)** – HTTP client that speaks the Vault/OpenBao API for common KV operations, unsealing, and enabling engines.
- **Commands** – `vault:unseal`, `vault:enable-engine`, and future operational helpers built on top of the service layer.
- **Configuration (`config/vault-suite.php`)** – Declares drivers, bootstrap behaviour, and caching options.
- **Bootstrap Blueprint (roadmap)** – A service provider hook that will hydrate environment variables during app boot using configured paths.

Refer to [docs/commands.md](commands.md) for detailed command usage and [docs/api.md](api.md) for programmatic access.

## Planned Drivers & Backends

- ✅ HashiCorp Vault KV (v1 & v2)
- ✅ OpenBao (leverages Vault driver)
- ⏳ Other providers (AWS Secrets Manager, GCP Secret Manager, Doppler, etc.) via community drivers.

## Roadmap

1. **Bootstrapper** – Implement the runtime bootstrap that maps configured secret paths to environment variables and caches results when enabled.
2. **Authentication Strategies** – Add AppRole, OIDC, and Kubernetes auth helpers for Vault/OpenBao.
3. **Additional Commands** – Helpers for sealing, rotating tokens, syncing secrets, and running health checks.
4. **Driver Contracts** – Publish guidance and helpers for authoring third-party drivers.

Progress is tracked on GitHub issues and in the [Changelog](changelog.md).

## Local Development Workflow

1. Clone the package alongside your Laravel application inside the multi-root workspace.
2. Register a Composer path repository in your app’s `composer.json` pointing to this package directory.
3. Require the package using the `deepdigs/laravel-vault-suite` constraint.
4. Develop against the path repository and run `composer update deepdigs/laravel-vault-suite` only when autoload or dependency changes occur.
5. When ready to publish, create a release tag and update downstream projects to use the Packagist version instead of the path repository.

## RawDigs Core App integration

1. Add a Composer path repository entry inside `rawdigs-core-app/composer.json` that points to `../laravel-vault-suite`.
2. Require the package locally: `composer require deepdigs/laravel-vault-suite:*@dev`.
3. Publish the configuration file: `php artisan vendor:publish --tag="vault-suite-config"`.
4. Configure `config/vault-suite.php` with your Vault/OpenBao endpoints and tokens (consider using your existing `scripts/vault-init.sh` only for initial bootstrap).
5. Drive operational flows via the new commands:
   - `php artisan vault:unseal --file=storage/keys/unseal.txt --reset`
   - `php artisan vault:enable-engine secret/rawdigs --option=version=2`
6. Configure the upcoming bootstrapper to hydrate `.env` values during application boot once implemented.

## Testing

Run the test suite via `composer test`. When working against Vault/OpenBao locally, prefer using docker-compose profiles that expose HTTPS endpoints to test SSL options.

## Security Notes

- Never commit real tokens or unseal keys into version control—lean on `.env` management and secret stores instead.
- Enable TLS verification in production and pin CA certificates where possible.
- Regularly rotate Vault tokens and leverage short-lived credentials.

## Contribution Guidelines

Contributions are welcome! Open an issue to discuss feature ideas or submit a pull request once tests are green. Follow PSR-12 coding style and run the static analysis presets (`composer analyse`) before submitting.
