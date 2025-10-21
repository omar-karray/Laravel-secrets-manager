# Artisan Commands

Laravel Vault Suite ships with artisan tooling that streamlines Vault/OpenBao administration and bootstrapping. All commands are auto-registered when the service provider is loaded.

## `vault:unseal`

Submit unseal key shards to the configured secrets backend.

```
php artisan vault:unseal key1 key2 key3
```

**Options**

| Option | Description |
| --- | --- |
| `--driver=` | Driver to use (defaults to `vault`). |
| `--reset` | Reset the unseal process before submitting the first key shard. |
| `--migrate` | Set the `migrate` flag on Vault’s unseal operation (used during seal type migrations). |
| `--file=` | Read key shards from a newline-separated file; comments starting with `#` are ignored. |

Keys passed via CLI arguments and files are merged. Duplicate and blank lines are automatically removed. The command halts with a non-zero exit code if Vault remains sealed after all keys have been submitted.

```bash
# Example: read keys from a file and reset the process
php artisan vault:unseal --file=/secure/path/unseal-keys.txt --reset
```

## `vault:enable-engine`

Enable or configure a secrets engine at the desired mount path.

```
php artisan vault:enable-engine secret/apps \
    --description="Application secrets" \
    --option=version=2 \
    --config=default_lease_ttl=3600 \
    --local
```

**Options**

| Option | Description |
| --- | --- |
| `--type=` | Secrets engine type (defaults to `kv`). |
| `--driver=` | Driver to use (defaults to `vault`). |
| `--description=` | Optional description for the mount. |
| `--option=` | Key/value pairs forwarded to engine options (e.g. `version=2`). Repeat for multiple entries. |
| `--config=` | Key/value pairs forwarded to engine configuration (`default_lease_ttl=3600`). Repeat as needed. |
| `--local` | Mount the secrets engine locally on the targeted node. |
| `--seal-wrap` | Enable seal wrapping at mount time. |

Values are automatically type-cast where possible (`true`, `false`, `null`, and numeric values are converted). Quoted strings are also supported: `--option='allowed_roles="app,worker"'`.

### Driver fallback

If you have configured multiple drivers (e.g. `vault` and `openbao`), both commands accept `--driver=openbao` to target the alternative backend without changing global configuration.
