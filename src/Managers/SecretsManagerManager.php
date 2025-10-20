<?php

namespace Deepdigs\LaravelSecretsManager\Managers;

use Deepdigs\LaravelSecretsManager\Contracts\SecretsDriver;
use Deepdigs\LaravelSecretsManager\Drivers\Vault\VaultSecretsDriver;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Manager;
use InvalidArgumentException;

class SecretsManagerManager extends Manager
{
    public function __construct(Container $container)
    {
        parent::__construct($container);
    }

    public function getDefaultDriver()
    {
        return $this->configRepository()->get('secrets-manager.default', 'vault');
    }

    /**
     * Retrieve a configured driver instance.
     */
    public function driver($driver = null): SecretsDriver
    {
        /** @var SecretsDriver $driver */
        $driver = parent::driver($driver);

        return $driver;
    }

    protected function createVaultDriver(array $config): SecretsDriver
    {
        return new VaultSecretsDriver(
            app(HttpFactory::class),
            $config
        );
    }

    protected function createOpenbaoDriver(array $config): SecretsDriver
    {
        // OpenBao shares the same API contract as Vault, so we reuse the driver implementation.
        return $this->createVaultDriver($config);
    }

    protected function createDriver($driver)
    {
        $config = $this->configRepository()->get("secrets-manager.drivers.{$driver}");

        if (is_null($config)) {
            throw new InvalidArgumentException("Secrets manager driver [{$driver}] is not configured.");
        }

        if (isset($this->customCreators[$driver])) {
            return $this->callCustomCreator($driver);
        }

        $method = 'create'.ucfirst($driver).'Driver';

        if (method_exists($this, $method)) {
            return $this->{$method}($config);
        }

        throw new InvalidArgumentException("Secrets manager driver [{$driver}] is not supported.");
    }

    protected function configRepository(): Repository
    {
        /** @var Repository $config */
        $config = app('config');

        return $config;
    }
}
