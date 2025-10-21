# Tutorials

This section walks through common workflows with Laravel Vault Suite—starting from a fresh install, wiring secrets into your application, and securing database credentials with Vault.

---

## 1. Daily command workflow

### Step 1 – Install & configure

```bash
composer require deepdigs/laravel-vault-suite
php artisan vendor:publish --tag="vault-suite-config"
```

Populate the environment variables referenced by `config/vault-suite.php`:

```dotenv
VAULT_SUITE_DRIVER=vault
VAULT_ADDR=https://vault.local
VAULT_TOKEN=<short-lived-token>
VAULT_ENGINE_MOUNT=secret
VAULT_ENGINE_VERSION=2
```

Tokens should be managed by your CI/CD or secret store—never commit them to source control.

### Step 2 – Verify connectivity

```bash
php artisan vault:status
php artisan vault:enable-engine secret/apps --option=version=2
```

- `vault:status` confirms the backend is reachable and whether it is sealed.
- `vault:enable-engine` validates write permissions by mounting a KV v2 engine (or reconfiguring an existing one).

### Step 3 – Unseal when required

When the cluster is sealed, submit key shares directly from Artisan:

```bash
php artisan vault:unseal --file=storage/keys/unseal.txt --reset
```

Use `--driver` if you operate multiple clusters (`--driver=openbao`).

### Step 4 – Inspect secrets

```bash
php artisan vault:list secret/apps
php artisan vault:read secret/apps/database --key=password --json
```

- `vault:list` mirrors Vault’s `LIST` operation, returning keys/directories.
- `vault:read` fetches a payload or specific key. Append `--json` for machine-readable output.

These commands can be chained inside shell scripts or CI jobs to automate operational checks.

---

## 2. Loading secrets into Laravel configuration

Until the built-in bootstrapper ships, hydrate configuration during the application bootstrapping phase. A simple service provider keeps secrets in sync without storing them in `.env` files:

```php
use Deepdigs\LaravelVaultSuite\LaravelVaultSuite;
use Illuminate\Support\ServiceProvider;

class VaultConfigServiceProvider extends ServiceProvider
{
    public function boot(LaravelVaultSuite $vault): void
    {
        if (app()->environment('local')) {
            return; // skip in development if you prefer .env
        }

        $database = $vault->fetch('secret/apps/database');

        config([
            'database.connections.mysql.username' => $database['username'],
            'database.connections.mysql.password' => $database['password'],
        ]);
    }
}
```

Register the provider in `config/app.php`. Because configuration is mutated at runtime:

- Run `php artisan config:clear` before fetching secrets so you are not reading stale cached values.
- After deploying, re-run `php artisan config:cache` once secrets are loaded. (When the bootstrapper is released you will map secrets in `config/vault-suite.php` and hydrate them before `config:cache` executes.)

### Responding to secret changes

If a secret changes in Vault:

1. Rotate/update it via `LaravelVaultSuite::put(...)` or manual CLI/API.
2. Redeploy or restart the application so the provider fetches the new value.
3. Clear and rebuild config cache if you are using it.

---

## 3. Securing database credentials with Vault

### Step 1 – Mount a dedicated engine

```bash
php artisan vault:enable-engine database/credentials --type=kv --option=version=2
```

### Step 2 – Store credentials

```php
$vault = app(Deepdigs\LaravelVaultSuite\LaravelVaultSuite::class);

$vault->put('database/credentials/mysql-app', [
    'username' => 'app',
    'password' => Str::random(40),
]);
```

Use Vault’s database secrets engine if you want automated rotation; the suite can still read the generated credentials with `vault:read` or the PHP API.

### Step 3 – Load credentials at boot

Reuse the provider pattern above, but point to the database secret:

```php
$creds = $vault->fetch('database/credentials/mysql-app');

config([
    'database.connections.mysql.username' => $creds['username'],
    'database.connections.mysql.password' => $creds['password'],
]);
```

### Step 4 – Rotate safely

1. Update the secret in Vault (`$vault->put(...)` or API call).
2. Redeploy the application (or trigger a restart) so the provider fetches the new values.
3. If you use `config:cache`, clear and rebuild it (`php artisan config:clear && php artisan config:cache`).

Consider scripting rotations with:

```bash
php artisan vault:read database/credentials/mysql-app --json
php artisan vault:read database/credentials/mysql-app --driver=openbao
```

and update target services accordingly.

---

## Best practices

- **Tokens:** use short-lived tokens (AppRole, OIDC, etc.). Do not store root tokens in `.env`.
- **Key shares:** keep unseal keys outside of source control. Read from secure storage into a temporary file when running `vault:unseal`.
- **Auditing:** enable Vault audit logging to track access initiated by commands or the PHP API.
- **Error handling:** catch `Deepdigs\LaravelVaultSuite\Exceptions\VaultSuiteException` when calling the API from PHP to surface backend errors gracefully.
- **Environment awareness:** guard secret loading with environment checks (e.g. skip in local tests if you do not run Vault).

For deeper dives, see the [Configuration Reference](configuration.md), [Programmatic API](api.md), and [Command Reference](commands.md).
