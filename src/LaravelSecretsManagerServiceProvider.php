<?php

namespace Deepdigs\LaravelSecretsManager;

use Deepdigs\LaravelSecretsManager\Commands\VaultEnableSecretsEngineCommand;
use Deepdigs\LaravelSecretsManager\Commands\VaultUnsealCommand;
use Deepdigs\LaravelSecretsManager\Managers\SecretsManagerManager;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelSecretsManagerServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-secrets-manager')
            ->hasConfigFile()
            ->hasCommands([
                VaultUnsealCommand::class,
                VaultEnableSecretsEngineCommand::class,
            ]);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(SecretsManagerManager::class, function ($app) {
            return new SecretsManagerManager($app);
        });

        $this->app->alias(SecretsManagerManager::class, 'secrets-manager.manager');

        $this->app->singleton(LaravelSecretsManager::class, function ($app) {
            return new LaravelSecretsManager($app->make(SecretsManagerManager::class));
        });

        $this->app->alias(LaravelSecretsManager::class, 'secrets-manager');
    }
}
