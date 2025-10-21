# Programmatic API

While Laravel Vault Suite is command-first, every feature is also exposed through PHP so you can integrate vault operations into jobs, controllers, or services.

## Resolving the service

You can inject `Deepdigs\LaravelVaultSuite\LaravelVaultSuite` or use the facade.

```php
use Deepdigs\LaravelVaultSuite\LaravelVaultSuite;

class SyncSecrets
{
    public function __construct(private LaravelVaultSuite $vault) {}

    public function handle(): void
    {
        $db = $this->vault->fetch('apps/laravel/database');
        config([
            'database.connections.mysql.username' => $db['username'],
            'database.connections.mysql.password' => $db['password'],
        ]);
    }
}
```

Or via the facade:

```php
use Deepdigs\LaravelVaultSuite\Facades\LaravelVaultSuite;

$password = LaravelVaultSuite::fetch('apps/laravel/database', 'password');
```

## Reading secrets

```php
$secret = $vault->fetch('kv/service-account');               // returns entire payload
$token = $vault->fetch('kv/service-account', 'token');        // return a key within the payload
$secretV1 = $vault->fetch('secret/team', null, ['version' => 1]);
$secretFromAltDriver = $vault->fetch('infra/creds', null, driver: 'openbao');
```

- Use the `driver` argument to target a non-default backend.
- Pass `version => 1` to talk to KV v1 engines.

## Writing and deleting

```php
$vault->put('kv/service-account', [
    'token' => Str::random(64),
    'created_at' => now()->toIso8601String(),
]);

$vault->delete('kv/service-account');
```

## Listing secrets

```php
$paths = $vault->list('kv');             // e.g. ['service-account/', 'payments/']
```

These listing results mirror Vault’s API (directories end with `/`).

## Seal status and unseal workflow

```php
$status = $vault->sealStatus();

if ($status['sealed']) {
    $vault->unseal([
        config('vault-suite.unseal.key1'),
        config('vault-suite.unseal.key2'),
    ], ['reset' => true]);
}
```

The `unseal()` method mirrors the command behaviour: submit key shards sequentially until the backend reports it is unsealed.

## Enabling secrets engines via code

```php
$vault->enableSecretsEngine('kv', 'secret/apps', [
    'description' => 'Application-level secrets',
    'options' => ['version' => 2],
], ['seal_wrap' => true]);
```

This wraps the same call the `vault:enable-engine` command issues. The fourth argument accepts engine-level options (e.g. `seal_wrap`, `local`).

## Working directly with drivers

To access the underlying driver instance:

```php
$driver = $vault->driver();              // resolves the default driver
$openbao = $vault->driver('openbao');

$config = $driver->sealStatus();         // interact with driver-specific capabilities
```

When writing a custom driver, implement `Deepdigs\LaravelVaultSuite\Contracts\SecretsDriver` and register it via the manager (`VaultSuiteManager::extend`).

## Error handling

The suite throws `Deepdigs\LaravelVaultSuite\Exceptions\VaultSuiteException` when the HTTP client reports a failure. Catch it to inspect the status code or message returned by the backend.

```php
use Deepdigs\LaravelVaultSuite\Exceptions\VaultSuiteException;

try {
    $vault->fetch('apps/laravel/unknown');
} catch (VaultSuiteException $e) {
    report($e);
}
```

That exception formats the backend’s error payload, making it easy to bubble up in logs or API responses.
