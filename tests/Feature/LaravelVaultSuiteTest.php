<?php

use Deepdigs\LaravelVaultSuite\Contracts\SecretsDriver;
use Deepdigs\LaravelVaultSuite\Facades\LaravelVaultSuite;
use Deepdigs\LaravelVaultSuite\LaravelVaultSuite as SuiteManager;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Config::set('vault-suite.drivers.vault', [
        'address' => 'https://vault.test',
        'token' => 'test-token',
        'engine' => [
            'mount' => 'secret',
            'version' => 2,
        ],
    ]);

    Http::preventStrayRequests();
});

it('fetches secret data and specific keys', function () {
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

    $data = LaravelVaultSuite::fetch('apps/laravel/database');
    $password = LaravelVaultSuite::fetch('apps/laravel/database', 'password');

    expect($data)->toMatchArray([
        'username' => 'laravel',
        'password' => 'secret',
    ]);
    expect($password)->toBe('secret');
});

it('writes, lists, and deletes secrets', function () {
    Http::fake([
        'https://vault.test/v1/secret/data/apps/laravel/api' => Http::sequence()
            ->push(['data' => ['version' => 1]], 200)
            ->push(null, 204),
        'https://vault.test/v1/secret/metadata/apps/laravel' => Http::response([
            'data' => ['keys' => ['api/', 'database/']],
        ], 200),
    ]);

    $write = LaravelVaultSuite::put('apps/laravel/api', [
        'token' => 'abc123',
    ]);

    expect($write)->toMatchArray(['version' => 1]);

    $keys = LaravelVaultSuite::list('apps/laravel');
    expect($keys)->toBe(['api/', 'database/']);

    LaravelVaultSuite::delete('apps/laravel/api');

    Http::assertSentCount(3);
});

it('exposes seal status helpers', function () {
    $sealStatuses = [
        ['sealed' => true, 'progress' => 1, 't' => 3],
        ['sealed' => true, 'progress' => 2, 't' => 3],
        ['sealed' => false, 'progress' => 3, 't' => 3],
    ];

    $unsealStatuses = [
        ['sealed' => true, 'progress' => 2, 't' => 3],
        ['sealed' => false, 'progress' => 3, 't' => 3],
    ];

    $captured = [];

    Http::fake(function ($request) use (&$sealStatuses, &$unsealStatuses, &$captured) {
        if (str_contains($request->url(), '/v1/sys/seal-status')) {
            $payload = array_shift($sealStatuses) ?? ['sealed' => false];

            return Http::response($payload, 200);
        }

        if (str_contains($request->url(), '/v1/sys/unseal')) {
            $captured[] = $request->data();
            $payload = array_shift($unsealStatuses) ?? ['sealed' => false];

            return Http::response($payload, 200);
        }

        return Http::response([], 404);
    });

    $status = LaravelVaultSuite::sealStatus();
    expect($status['sealed'])->toBeTrue();

    $unsealed = LaravelVaultSuite::unseal(['key-1', 'key-2', 'key-3'], ['reset' => true]);

    expect($unsealed['sealed'])->toBeFalse();
    expect($captured)->not->toBeEmpty();
    expect($captured[0]['reset'] ?? false)->toBeTrue();
});

it('supports driver resolution via container alias', function () {
    /** @var SuiteManager $manager */
    $manager = app(SuiteManager::class);

    expect($manager->driver())->toBeInstanceOf(SecretsDriver::class);
});
