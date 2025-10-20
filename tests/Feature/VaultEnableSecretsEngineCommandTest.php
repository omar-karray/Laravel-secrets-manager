<?php

use Deepdigs\LaravelVaultSuite\LaravelVaultSuite;
use Mockery as M;

use function Pest\Laravel\artisan;

it('parses command options and forwards them to the secrets manager', function () {
    $mock = M::mock(LaravelVaultSuite::class);

    app()->instance(LaravelVaultSuite::class, $mock);
    app()->alias(LaravelVaultSuite::class, 'laravel-vault-suite');

    $mock->shouldReceive('enableSecretsEngine')
        ->once()
        ->with(
            'kv',
            'secret/apps',
            [
                'description' => 'Application secrets',
                'config' => ['default_lease_ttl' => 3600],
                'options' => ['version' => 2],
            ],
            ['local' => true],
            'vault'
        )
        ->andReturn([]);

    artisan('vault:enable-engine', [
        'path' => 'secret/apps',
        '--description' => 'Application secrets',
        '--option' => ['version=2'],
        '--config' => ['default_lease_ttl=3600'],
        '--local' => true,
    ])->assertExitCode(0);
});

it('casts option values and forwards seal wrap flag', function () {
    $mock = M::mock(LaravelVaultSuite::class);

    app()->instance(LaravelVaultSuite::class, $mock);
    app()->alias(LaravelVaultSuite::class, 'laravel-vault-suite');

    $mock->shouldReceive('enableSecretsEngine')
        ->once()
        ->with(
            'database',
            'database/creds',
            [
                'options' => ['allowed_roles' => 'app,worker'],
                'config' => ['max_lease_ttl' => 7200],
            ],
            ['seal_wrap' => true],
            'vault'
        )
        ->andReturn([]);

    artisan('vault:enable-engine', [
        'path' => 'database/creds',
        '--type' => 'database',
        '--option' => ['allowed_roles="app,worker"'],
        '--config' => ['max_lease_ttl=7200'],
        '--seal-wrap' => true,
    ])->assertExitCode(0);
});
