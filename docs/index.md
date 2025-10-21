# Laravel Vault Suite

Laravel Vault Suite is a command-focused integration layer between Laravel and Vault-compatible backends (HashiCorp Vault, OpenBao, …). It helps teams remove secrets from `.env` files, automate vault operations, and keep secure configuration close to the framework they already know.

## What you get

| Capability | Summary |
| --- | --- |
| **Operational tooling** | Purpose-built Artisan commands (`vault:unseal`, `vault:enable-engine`, …) for the workflows operators and developers run every day. |
| **Fluent PHP API** | A service/facade you can inject to read, write, list, and delete secrets from your code. |
| **Driver system** | Vault and OpenBao support out-of-the-box with an extensible contract for additional secret backends. |
| **Roadmap-ready hooks** | Configuration bootstrapper, caching, and auth helpers are planned to support production deployments. |

## Quick start

```bash
composer require deepdigs/laravel-vault-suite
php artisan vendor:publish --tag="vault-suite-config"

# Unseal a Vault instance using key shards stored on disk
php artisan vault:unseal --file=storage/keys/unseal.txt --reset

# Mount a KV engine ready for application secrets
php artisan vault:enable-engine secret/apps --option=version=2
```

Check the [Artisan Commands](commands.md) guide for a breakdown of every flag, example output, and common failure scenarios.

## Primary commands at a glance

| Command | When to use it |
| --- | --- |
| `vault:unseal` | Automate the unseal process by submitting key shards (CLI arguments or file-based). |
| `vault:enable-engine` | Mount or reconfigure secrets engines (KV v1/v2, database, custom engines). |
| `vault:enable-engine --driver=openbao` | Target an alternative backend without changing global configuration. |

## Documentation map

- **[Getting Started](getting-started.md)** – Preconditions, installation paths (Packagist and path repo), and first-run validation.
- **[Artisan Commands](commands.md)** – Detailed reference for the operational CLI surface.
- **[Configuration Reference](configuration.md)** – Complete explanation of `config/vault-suite.php`, environment variables, caching, and bootstrap mapping.
- **[Programmatic API](api.md)** – Working with the service container, facade, and driver manager from pure PHP.
- **[Architecture](context.md)** – Internal design, driver creation, and roadmap.
- **[Integrations](integration/rawdigs-core-app.md)** – Wiring the suite into the RawDigs multi-root workspace.
- **[Deployment](deployment.md)** – Building and publishing these docs to GitHub Pages.
- **[Changelog](changelog.md)** – Release highlights.

Want to contribute or report an issue? Use the links in the sidebar or jump straight to GitHub.
