<?php

use Deepdigs\LaravelVaultSuite\LaravelVaultSuite;
use Mockery as M;

use function Pest\Laravel\artisan;

it('reads unseal keys from a file when requested', function () {
    $tempFile = tempnam(sys_get_temp_dir(), 'vault-keys');
    file_put_contents($tempFile, "key-1\n# a comment\nkey-2\n");

    $mock = M::mock(LaravelVaultSuite::class);
    app()->instance(LaravelVaultSuite::class, $mock);
    app()->alias(LaravelVaultSuite::class, 'laravel-vault-suite');

    $mock->shouldReceive('unseal')
        ->once()
        ->with(
            ['key-1', 'key-2'],
            ['reset' => true, 'migrate' => true],
            'vault'
        )
        ->andReturn(['sealed' => false]);

    try {
        artisan('vault:unseal', [
            '--file' => $tempFile,
            '--reset' => true,
            '--migrate' => true,
        ])->assertExitCode(0);
    } finally {
        @unlink($tempFile);
    }
});

it('fails when vault remains sealed after submitting keys', function () {
    $mock = M::mock(LaravelVaultSuite::class);
    app()->instance(LaravelVaultSuite::class, $mock);
    app()->alias(LaravelVaultSuite::class, 'laravel-vault-suite');

    $mock->shouldReceive('unseal')
        ->once()
        ->andReturn(['sealed' => true, 'progress' => 2, 't' => 3]);

    artisan('vault:unseal', [
        'keys' => ['key-1', 'key-2'],
    ])->assertExitCode(1);
});

it('fails when the provided key file cannot be read', function () {
    $mock = M::mock(LaravelVaultSuite::class);
    $mock->shouldNotReceive('unseal');

    app()->instance(LaravelVaultSuite::class, $mock);
    app()->alias(LaravelVaultSuite::class, 'laravel-vault-suite');

    artisan('vault:unseal', [
        '--file' => '/path/does/not/exist',
    ])->assertExitCode(1);
});

it('fails when no keys are supplied', function () {
    $mock = M::mock(LaravelVaultSuite::class);
    $mock->shouldNotReceive('unseal');

    app()->instance(LaravelVaultSuite::class, $mock);
    app()->alias(LaravelVaultSuite::class, 'laravel-vault-suite');

    artisan('vault:unseal')->assertExitCode(1);
});
