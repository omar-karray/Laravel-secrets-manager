<?php

use Deepdigs\LaravelVaultSuite\LaravelVaultSuite;
use Mockery as M;
use function Pest\Laravel\artisan;

it('lists secrets for a path', function () {
    $mock = M::mock(LaravelVaultSuite::class);
    $mock->shouldReceive('list')->once()->with('apps', [], 'vault')->andReturn(['db/', 'api/']);

    app()->instance(LaravelVaultSuite::class, $mock);
    app()->alias(LaravelVaultSuite::class, 'LaravelVaultSuite');

    artisan('vault:list', ['path' => 'apps'])
        ->expectsOutputToContain('- db/')
        ->expectsOutputToContain('- api/')
        ->assertExitCode(0);
});

it('passes optional driver and version overrides to list command', function () {
    $mock = M::mock(LaravelVaultSuite::class);
    $mock->shouldReceive('list')->once()->with('services', ['mount' => 'secret', 'version' => 1], 'openbao')->andReturn([]);

    app()->instance(LaravelVaultSuite::class, $mock);
    app()->alias(LaravelVaultSuite::class, 'LaravelVaultSuite');

    artisan('vault:list', ['path' => 'services', '--driver' => 'openbao', '--mount' => 'secret', '--engine-version' => 1])
        ->assertExitCode(0);
});
