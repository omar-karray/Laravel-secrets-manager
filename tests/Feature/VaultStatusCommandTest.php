<?php

use Deepdigs\LaravelVaultSuite\LaravelVaultSuite;
use Mockery as M;
use function Pest\Laravel\artisan;

it('displays vault seal status', function () {
    $mock = M::mock(LaravelVaultSuite::class);
    $mock->shouldReceive('sealStatus')->once()->with([], 'vault')->andReturn([
        'sealed' => false,
        'progress' => 3,
        't' => 3,
        'initialized' => true,
        'cluster_name' => 'vault-dev',
        'version' => '1.15.0',
    ]);

    app()->instance(LaravelVaultSuite::class, $mock);
    app()->alias(LaravelVaultSuite::class, 'LaravelVaultSuite');

    artisan('vault:status')
        ->expectsOutputToContain('Driver')
        ->expectsOutputToContain('vault-dev')
        ->assertExitCode(0);
});
