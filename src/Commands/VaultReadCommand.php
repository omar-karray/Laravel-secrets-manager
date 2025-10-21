<?php

namespace Deepdigs\LaravelVaultSuite\Commands;

use Deepdigs\LaravelVaultSuite\LaravelVaultSuite;
use Illuminate\Console\Command;

class VaultReadCommand extends Command
{
    protected $signature = 'vault:read {path : Secret path to fetch} {--key= : Specific key within the secret payload} {--driver=vault : Secrets backend driver to target} {--mount= : Override the secrets engine mount} {--engine-version= : KV engine version (1 or 2)} {--json : Output raw JSON}';

    protected $description = 'Read a secret from the configured vault backend';

    public function handle(LaravelVaultSuite $vault): int
    {
        $path = (string) $this->argument('path');
        $options = $this->buildOptions();

        $result = $vault->fetch(
            $path,
            $this->option('key') ?: null,
            $options,
            $this->option('driver')
        );

        if ($this->option('json')) {
            $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        if (! is_array($result)) {
            $this->line((string) $result);

            return self::SUCCESS;
        }

        if (empty($result)) {
            $this->components->warn('Secret payload is empty.');

            return self::SUCCESS;
        }

        foreach ($result as $key => $value) {
            $this->components->twoColumnDetail((string) $key, is_scalar($value) ? (string) $value : json_encode($value));
        }

        return self::SUCCESS;
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildOptions(): array
    {
        $options = [];

        if ($mount = $this->option('mount')) {
            $options['mount'] = trim((string) $mount, '/');
        }

        if (($version = $this->option('engine-version')) !== null && $version !== '') {
            $options['version'] = (int) $version;
        }

        return $options;
    }
}
