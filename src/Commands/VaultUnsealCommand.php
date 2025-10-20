<?php

namespace Deepdigs\LaravelVaultSuite\Commands;

use Deepdigs\LaravelVaultSuite\LaravelVaultSuite;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class VaultUnsealCommand extends Command
{
    protected $signature = 'vault:unseal
        {keys?* : The unseal key shards to submit to Vault}
        {--driver=vault : The secrets driver to use}
        {--reset : Reset the unseal process before submitting keys}
        {--migrate : Migrate the recovery seal if required}
        {--file= : Path to a file containing newline separated key shards}';

    protected $description = 'Submit key shards to unseal the configured Vault backend';

    public function handle(LaravelVaultSuite $secretsManager): int
    {
        $keys = $this->gatherKeys();

        if ($keys === null || empty($keys)) {
            if ($keys !== null) {
                $this->error('No unseal keys were provided.');
            }

            return self::FAILURE;
        }

        $options = [
            'reset' => (bool) $this->option('reset'),
            'migrate' => (bool) $this->option('migrate'),
        ];

        $status = $secretsManager->unseal(
            $keys,
            $options,
            $this->option('driver')
        );

        if (Arr::get($status, 'sealed') === false) {
            $this->info('Vault has been unsealed successfully.');

            return self::SUCCESS;
        }

        $this->warn(sprintf(
            'Vault remains sealed. Progress: %s/%s key shares submitted.',
            Arr::get($status, 'progress', 0),
            Arr::get($status, 't', '?')
        ));

        return self::FAILURE;
    }

    /**
     * Gather unseal keys from CLI arguments and optional file input.
     *
     * @return array<int, string>|null
     */
    protected function gatherKeys(): ?array
    {
        $keys = $this->normalizeKeys(Arr::wrap($this->argument('keys')));

        $fileOption = $this->option('file');

        if (is_array($fileOption)) {
            $this->error('Multiple file values are not supported.');

            return null;
        }

        if (is_string($fileOption) && $fileOption !== '') {
            if (! is_readable($fileOption)) {
                $this->error(sprintf('Unable to read unseal keys from [%s].', $fileOption));

                return null;
            }

            $fileKeys = $this->normalizeKeys(file($fileOption, FILE_IGNORE_NEW_LINES) ?: []);

            $keys = array_merge($keys, $fileKeys);
        } elseif ($fileOption !== null && $fileOption !== '') {
            $this->error('The provided key file path is invalid.');

            return null;
        }

        $keys = array_values(array_unique(array_filter($keys, fn ($key) => $key !== '')));

        return $keys;
    }

    /**
     * @param  array<int, string|null>  $keys
     * @return array<int, string>
     */
    protected function normalizeKeys(array $keys): array
    {
        return array_values(array_filter(array_map(function ($key) {
            $trimmed = is_string($key) ? trim($key) : null;

            if ($trimmed === null || $trimmed === '' || str_starts_with($trimmed, '#')) {
                return null;
            }

            return $trimmed;
        }, $keys)));
    }
}
