<?php

use Deepdigs\LaravelVaultSuite\Drivers\Vault\VaultSecretsDriver;
use Deepdigs\LaravelVaultSuite\Exceptions\VaultSuiteException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::preventStrayRequests();
});

function makeDriver(array $config = []): VaultSecretsDriver
{
    return new VaultSecretsDriver(
        app(HttpFactory::class),
        array_merge([
            'address' => 'https://vault.test',
            'token' => 'test-token',
            'engine' => [
                'mount' => 'secret',
                'version' => 2,
            ],
        ], $config)
    );
}

it('reads kv v2 secrets', function () {
    Http::fake([
        'https://vault.test/v1/secret/data/apps/laravel/database' => Http::response([
            'data' => [
                'data' => [
                    'username' => 'laravel',
                    'password' => 'secret',
                ],
            ],
        ], 200),
    ]);

    $driver = makeDriver();

    $secret = $driver->read('apps/laravel/database');

    expect($secret)->toMatchArray([
        'username' => 'laravel',
        'password' => 'secret',
    ]);

    Http::assertSent(function ($request) {
        return $request->method() === 'GET'
            && $request->url() === 'https://vault.test/v1/secret/data/apps/laravel/database';
    });
});

it('reads kv v1 secrets when version override provided', function () {
    Http::fake([
        'https://vault.test/v1/secret/apps/laravel' => Http::response([
            'data' => [
                'token' => 'abc123',
            ],
        ], 200),
    ]);

    $driver = makeDriver();

    $secret = $driver->read('apps/laravel', ['version' => 1]);

    expect($secret)->toMatchArray([
        'token' => 'abc123',
    ]);

    Http::assertSent(fn ($request) => $request->url() === 'https://vault.test/v1/secret/apps/laravel');
});

it('writes secrets to kv v2', function () {
    Http::fake([
        'https://vault.test/v1/secret/data/apps/laravel/database' => Http::response([
            'data' => [
                'version' => 2,
            ],
        ], 200),
    ]);

    $driver = makeDriver();

    $response = $driver->write('apps/laravel/database', [
        'username' => 'laravel',
        'password' => 'secret',
    ]);

    expect($response)->toMatchArray(['version' => 2]);

    Http::assertSent(function ($request) {
        return $request->method() === 'POST'
            && $request->url() === 'https://vault.test/v1/secret/data/apps/laravel/database'
            && $request['data'] === [
                'username' => 'laravel',
                'password' => 'secret',
            ];
    });
});

it('deletes secrets', function () {
    Http::fake([
        'https://vault.test/v1/secret/data/apps/laravel/database' => Http::response(null, 204),
    ]);

    $driver = makeDriver();

    $driver->delete('apps/laravel/database');

    Http::assertSent(fn ($request) => $request->method() === 'DELETE');
});

it('lists secrets from metadata endpoint', function () {
    Http::fake([
        'https://vault.test/v1/secret/metadata/apps/laravel' => Http::response([
            'data' => [
                'keys' => ['database/', 'cache/'],
            ],
        ], 200),
    ]);

    $driver = makeDriver();

    $keys = $driver->list('apps/laravel');

    expect($keys)->toBe(['database/', 'cache/']);

    Http::assertSent(function ($request) {
        return $request->method() === 'LIST'
            && $request->url() === 'https://vault.test/v1/secret/metadata/apps/laravel'
            && $request->hasHeader('X-Vault-Request', 'true');
    });
});

it('enables secrets engines with extended options', function () {
    Http::fake([
        'https://vault.test/v1/sys/mounts/secret/apps' => Http::response([], 204),
    ]);

    $driver = makeDriver();

    $driver->enableSecretsEngine('kv', 'secret/apps', [
        'description' => 'Application secrets',
        'options' => ['version' => 2],
        'config' => ['default_lease_ttl' => 3600],
    ], ['local' => true, 'seal_wrap' => true]);

    Http::assertSent(function ($request) {
        return $request->method() === 'POST'
            && $request->url() === 'https://vault.test/v1/sys/mounts/secret/apps'
            && $request['type'] === 'kv'
            && $request['options'] === ['version' => 2]
            && $request['config'] === ['default_lease_ttl' => 3600]
            && $request['local'] === true
            && $request['seal_wrap'] === true;
    });
});

it('reports seal status and submits unseal keys', function () {
    Http::fake([
        'https://vault.test/v1/sys/seal-status' => Http::response(['sealed' => true, 'progress' => 0, 't' => 3], 200),
        'https://vault.test/v1/sys/unseal' => Http::response(['sealed' => false, 'progress' => 3, 't' => 3], 200),
    ]);

    $driver = makeDriver();

    $status = $driver->sealStatus();
    expect($status['sealed'])->toBeTrue();

    $unsealed = $driver->submitUnsealKey('key-1', ['reset' => true]);
    expect($unsealed)->toMatchArray(['sealed' => false, 'progress' => 3, 't' => 3]);
});

it('throws exception when vault responds with error', function () {
    Http::fake([
        'https://vault.test/v1/secret/data/apps/laravel/database' => Http::response([
            'errors' => ['permission denied'],
        ], 403),
    ]);

    $driver = makeDriver();

    expect(fn () => $driver->read('apps/laravel/database'))
        ->toThrow(VaultSuiteException::class, 'permission denied');
});
