<?php

namespace Deepdigs\LaravelVaultSuite;

use Deepdigs\LaravelVaultSuite\Contracts\SecretsDriver;
use Deepdigs\LaravelVaultSuite\Managers\VaultSuiteManager;
use Illuminate\Support\Arr;

class LaravelVaultSuite
{
    public function __construct(
        protected VaultSuiteManager $manager
    ) {}

    /**
     * Resolve a secrets driver instance by name.
     */
    public function driver(?string $name = null): SecretsDriver
    {
        return $this->manager->driver($name);
    }

    /**
     * Fetch a secret from the configured secrets backend.
     *
     * @return array<string, mixed>|mixed
     */
    public function fetch(string $path, ?string $key = null, array $options = [], ?string $driver = null)
    {
        $data = $this->driver($driver)->read($path, $options);

        if ($key === null) {
            return $data;
        }

        return Arr::get($data, $key);
    }

    /**
     * Persist secret data to the backend.
     *
     * @param  array<string, mixed>  $payload
     */
    public function put(string $path, array $payload, array $options = [], ?string $driver = null): array
    {
        return $this->driver($driver)->write($path, $payload, $options);
    }

    /**
     * Remove secret data from the backend.
     */
    public function delete(string $path, array $options = [], ?string $driver = null): void
    {
        $this->driver($driver)->delete($path, $options);
    }

    /**
     * List secrets stored beneath a path.
     *
     * @return array<int, string>
     */
    public function list(string $path, array $options = [], ?string $driver = null): array
    {
        return $this->driver($driver)->list($path, $options);
    }

    /**
     * Retrieve the current seal status for the backend.
     *
     * @return array<string, mixed>
     */
    public function sealStatus(array $options = [], ?string $driver = null): array
    {
        return $this->driver($driver)->sealStatus($options);
    }

    /**
     * Attempt to unseal the backend by submitting key shards sequentially.
     *
     * @param  string[]  $keys
     * @return array<string, mixed>
     */
    public function unseal(array $keys, array $options = [], ?string $driver = null): array
    {
        $driver = $this->driver($driver);
        $status = $driver->sealStatus($options);

        if (Arr::get($status, 'sealed') === false) {
            return $status;
        }

        $options = $options ?? [];

        foreach ($keys as $index => $key) {
            $perKeyOptions = $options;
            $perKeyOptions['reset'] = (bool) Arr::get($options, 'reset', false) && $index === 0;

            $status = $driver->submitUnsealKey($key, $perKeyOptions);

            if (Arr::get($status, 'sealed') === false) {
                break;
            }
        }

        return $status;
    }

    /**
     * Enable a secrets engine for the backend.
     *
     * @param  array<string, mixed>  $settings
     * @return array<string, mixed>
     */
    public function enableSecretsEngine(string $type, string $path, array $settings = [], array $options = [], ?string $driver = null): array
    {
        return $this->driver($driver)->enableSecretsEngine($type, $path, $settings, $options);
    }
}
