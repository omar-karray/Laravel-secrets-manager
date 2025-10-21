# Laravel Vault Suite

Laravel Vault Suite is a command-driven toolkit that wires Laravel applications to Vault-compatible backends (HashiCorp Vault, OpenBao, …). It focuses on operational workflows—unsealing, enabling engines, reading/writing secrets—exposed through a first-class Artisan experience, while still offering a fluent PHP API when you need it.

## Highlights

- **Operational first** – Ship-ready Artisan commands (`vault:unseal`, `vault:enable-engine`, …) designed for day-to-day vault administration.
- **Vault-native API** – Read, write, list, and delete secrets using familiar Laravel patterns when you need programmatic access.
- **Extensible drivers** – Vault and OpenBao support out of the box, with a contract for additional providers.
- **Production ready** – Designed for multi-environment deployments with configuration, caching, and bootstrap hooks on the roadmap.

## Quick tour

```bash
composer require deepdigs/laravel-vault-suite
php artisan vendor:publish --tag="vault-suite-config"

# Unseal a Vault instance from key shares stored on disk
php artisan vault:unseal --file=storage/keys/unseal.txt --reset

# Mount a KV engine ready for your application secrets
php artisan vault:enable-engine secret/apps --option=version=2
```

The [Artisan Commands](commands.md) page covers every flag and option in detail.

## Documentation sections

- [Getting Started](getting-started.md) – Install and configure the package in development or production.
- [Artisan Commands](commands.md) – Operate Vault/OpenBao directly from artisan.
- [Architecture](context.md) – Dive into package internals and roadmap.
- [Integrations](integration/rawdigs-core-app.md) – Specific guidance for RawDigs and other apps.
- [Deployment](deployment.md) – Build and publish these docs to GitHub Pages.

Use the navigation sidebar to explore the rest of the documentation.
