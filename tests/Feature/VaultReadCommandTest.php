<?php

use Deepdigs\LaravelVaultSuite\LaravelVaultSuite;
use Mockery as M;
use function Pest\Laravel\artisan;

it('reads a full secret payload', function () {
    $mock = M::mock(LaravelVaultSuite::class);
    $mock->shouldReceive('fetch')
        ->once()
        ->with('apps/database', null, [], 'vault')
        ->andReturn(['username' => 'laravel', 'password' => 'secret']);

    app()->instance(LaravelVaultSuite::class, $mock);
    app()->alias(LaravelVaultSuite::class, 'LaravelVaultSuite');

    artisan('vault:read', ['path' => 'apps/database'])
        ->expectsOutputToContain('username')
        ->expectsOutputToContain('password')
        ->assertExitCode(0);
});

it('reads a specific key and prints JSON when requested', function () {
    $mock = M::mock(LaravelVaultSuite::class);
    $mock->shouldReceive('fetch')
        ->once()
        ->with('apps/database', 'password', ['mount' => 'secret', 'version' => 2], 'openbao')
        ->andReturn('super-secret');

    app()->instance(LaravelVaultSuite::class, $mock);
    app()->alias(LaravelVaultSuite::class, 'LaravelVaultSuite');

    artisan('vault:read', [
        'path' => 'apps/database',
        '--key' => 'password',
        '--driver' => 'openbao',
        '--mount' => 'secret',
        '--engine-version' => 2,
        '--json' => true,
    ])->expectsOutputToContain('"super-secret"')
      ->assertExitCode(0);
});
