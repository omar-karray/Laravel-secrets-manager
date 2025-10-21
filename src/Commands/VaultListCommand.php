<?php

namespace Deepdigs\LaravelVaultSuite\Commands;

use Deepdigs\LaravelVaultSuite\LaravelVaultSuite;
use Illuminate\Console\Command;

class VaultListCommand extends Command
{
    protected $signature = 'vault:list {path : Secrets path to list} {--driver=vault : Secrets backend driver to target} {--mount= : Override the secrets engine mount} {--engine-version= : KV engine version (1 or 2)}';

    protected $description = 'List secrets beneath a given path';

    public function handle(LaravelVaultSuite $vault): int
    {
        $path = (string) $this->argument('path');
        $options = $this->buildOptions();

        $keys = $vault->list($path, $options, $this->option('driver'));

        if (empty($keys)) {
            $this->components->warn('No secrets found.');

            return self::SUCCESS;
        }

        $this->components->info(sprintf('Secrets beneath %s:', trim($path, '/')));

        foreach ($keys as $key) {
            $this->line('- '.$key);
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
