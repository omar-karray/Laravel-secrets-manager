# Artisan Commands

Laravel Vault Suite ships with operational commands for the workflows engineers run daily. Commands are auto-registered by the service provider once the package is installed.

Before you begin:

- Ensure your vault backend is reachable from the environment running the command (`VAULT_ADDR`, `VAULT_TOKEN`, …).
- If you need a different backend per command, pass `--driver=<name>`; the driver must be configured under `config/vault-suite.php`.

---

## `vault:unseal`

Submit key shares to unseal a Vault/OpenBao cluster.

```bash
php artisan vault:unseal key1 key2 key3
```

### Options

| Flag | Description |
| --- | --- |
| `keys*` argument | One or more key shares passed inline. The command submits them sequentially. |
| `--file=` | Path to a newline-separated file containing key shares. Comments starting with `#` are ignored. |
| `--driver=` | Override the backend driver for this run (defaults to the configuration’s `default`). |
| `--reset` | Reset the unseal process before submitting the first key. Useful when vault reports partial progress from a previous attempt. |
| `--migrate` | Pass the `migrate` flag to the backend when migrating seal types. |

### Example

```bash
php artisan vault:unseal --file=storage/keys/unseal.txt --reset
```

Typical output:

```
Vault remains sealed. Progress: 2/3 key shares submitted.
```

If the backend is successfully unsealed you will see:

```
Vault has been unsealed successfully.
```

**Exit codes**

- `0` – Vault is unsealed.
- `1` – Vault remains sealed or the command could not read the provided keys.

---

## `vault:enable-engine`

Enable or reconfigure a secrets engine.

```bash
php artisan vault:enable-engine secret/apps --option=version=2
```

### Options

| Flag | Description |
| --- | --- |
| `path` argument | The mount path for the engine (e.g. `secret/apps`). |
| `--type=` | Engine type (defaults to `kv`). |
| `--driver=` | Backend driver override. |
| `--description=` | Optional mount description. |
| `--option=` | Key/value pair forwarded to `options`. Repeat for multiple options (e.g. `--option=allowed_roles="app,worker"`). |
| `--config=` | Key/value pair forwarded to the engine `config`. Repeat as required (e.g. `--config=default_lease_ttl=3600`). |
| `--local` | Mount locally on the targeted node only. |
| `--seal-wrap` | Enable seal wrapping for the mounted engine. |

### Casting values

Boolean strings (`true`, `false`), `null`, and numeric values are automatically cast. Quoted strings preserve punctuation:

```bash
php artisan vault:enable-engine database/creds \
    --type=database \
    --option='allowed_roles="app,worker"' \
    --config=max_lease_ttl=7200 \
    --seal-wrap
```

### Exit codes

- `0` – Engine mounted or reconfigured successfully.
- `1` – Validation or API error (the command writes the backend error payload to the console).

---

## Choosing a driver per command

All commands accept `--driver=<name>` to target a backend defined in `config/vault-suite.php`:

```bash
php artisan vault:enable-engine secret/apps --driver=openbao
```

Drivers share the same interface, so adding a custom backend via `VaultSuiteManager::extend()` automatically makes it available to the CLI.

---

## Coming soon

- Authentication helpers (AppRole, OIDC, Kubernetes) to mint tokens prior to running commands.
- Commands for secret rotation and health checks.
- Bootstrapper integration to hydrate Laravel configuration during `artisan config:cache`.

Track progress in the [Architecture](context.md) section or on the GitHub issue tracker.
