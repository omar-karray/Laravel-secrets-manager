# Artisan Commands

Laravel Vault Suite ships with operational commands for the workflows engineers run daily. Each command accepts a `--driver` flag so you can target any backend defined in `config/vault-suite.php`.

Before running commands:

- Ensure the backend is reachable (`VAULT_ADDR`, `VAULT_TOKEN`, …).
- Use service accounts with least-privilege policies.
- If you need to switch backends per command, supply `--driver=<name>`.

---

## `vault:status`

Shows the current seal status of the configured backend.

```bash
php artisan vault:status --driver=openbao
```

The command prints cluster information and whether additional key shares are required. Exit code is always `0`; check the `Sealed` column for state.

---

## `vault:unseal`

Submit key shares to unseal a Vault/OpenBao cluster.

```bash
php artisan vault:unseal key1 key2 key3
```

| Argument / Option | Description |
| --- | --- |
| `keys*` | Key shares passed inline; they are submitted sequentially. |
| `--file=` | Path to newline-separated key shares (comments starting with `#` are ignored). |
| `--driver=` | Override the backend driver (defaults to configuration). |
| `--reset` | Reset the unseal process before providing key shares. |
| `--migrate` | Pass the `migrate` flag when changing seal types. |

Exit codes: `0` when unsealed, `1` when the backend remains sealed or keys cannot be read.

---

## `vault:enable-engine`

Enable or reconfigure a secrets engine.

```bash
php artisan vault:enable-engine secret/apps --option=version=2
```

| Option | Description |
| --- | --- |
| `path` (argument) | Mount path for the secrets engine. |
| `--type=` | Engine type (`kv`, `database`, …). Defaults to `kv`. |
| `--driver=` | Backend driver override. |
| `--description=` | Optional human-friendly description. |
| `--option=` | Key/value forwarded to the engine `options` (repeatable). |
| `--config=` | Key/value forwarded to engine `config` (repeatable). |
| `--local` | Mount locally on the current node only. |
| `--seal-wrap` | Enable seal wrapping for the engine. |

Boolean strings (`true`, `false`), `null`, and numerics are automatically cast. Quote complex strings: `--option='allowed_roles="app,worker"'`.

---

## `vault:list`

List secrets beneath a path.

```bash
php artisan vault:list secret/apps --driver=openbao --engine-version=1
```

| Option | Description |
| --- | --- |
| `path` (argument) | Secret path to list. |
| `--driver=` | Backend driver override. |
| `--mount=` | Override the mount configured in `config/vault-suite.php`. |
| `--engine-version=` | KV engine version (`1` or `2`). |

Outputs each key on its own line. Directories are suffixed with `/` following Vault’s API conventions.

---

## `vault:read`

Fetch a secret (or a specific key within it).

```bash
php artisan vault:read secret/apps/database --key=password --json
```

| Option | Description |
| --- | --- |
| `path` (argument) | Secret path to read. |
| `--key=` | Return a single key from the secret payload. |
| `--driver=` | Backend driver override. |
| `--mount=` | Override the mount configured in `config/vault-suite.php`. |
| `--engine-version=` | KV engine version (`1` or `2`). |
| `--json` | Output raw JSON instead of table formatting. |

When `--key` is not supplied, the command prints each key/value pair. Use `--json` for machine-readable output.

---

## Tips

- All commands return `0` on success and `1` on validation/backend failure (except `vault:status`, which always succeeds and leaves state inspection to you).
- Combine commands inside shell scripts or CI jobs to automate vault operations during deployments.
- Need a feature that is not exposed yet? The underlying [Programmatic API](api.md) gives you full access to the service layer.

Future additions—auth helpers, secret rotation, health checks—are tracked on the project roadmap.
