<?php

namespace Deepdigs\LaravelVaultSuite;

use Deepdigs\LaravelVaultSuite\Commands\VaultEnableSecretsEngineCommand;
use Deepdigs\LaravelVaultSuite\Commands\VaultUnsealCommand;
use Deepdigs\LaravelVaultSuite\Managers\VaultSuiteManager;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelVaultSuiteServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-vault-suite')
            ->hasConfigFile('vault-suite')
            ->hasCommands([
                VaultUnsealCommand::class,
                VaultEnableSecretsEngineCommand::class,
            ]);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(VaultSuiteManager::class);
        $this->app->alias(VaultSuiteManager::class, 'vault-suite.manager');
        $this->app->singleton(LaravelVaultSuite::class);
        $this->app->alias(LaravelVaultSuite::class, 'LaravelVaultSuite');
    }
}
