<?php

namespace Deepdigs\LaravelSecretsManager\Commands;

use Deepdigs\LaravelSecretsManager\LaravelSecretsManager;
use Illuminate\Console\Command;

class VaultEnableSecretsEngineCommand extends Command
{
    protected $signature = 'vault:enable-engine
        {path : The mount path for the secrets engine}
        {--type=kv : The type of secrets engine to enable}
        {--driver=vault : The secrets driver to use}
        {--description= : Optional description for the mount}
        {--option=* : Key=value options forwarded to the engine configuration}
        {--config=* : Key=value configuration forwarded to the engine}
        {--local : Mount the secrets engine locally}
        {--seal-wrap : Enable seal wrapping for the secrets engine}';

    protected $description = 'Enable a secrets engine on the configured Vault backend';

    public function handle(LaravelSecretsManager $secretsManager): int
    {
        $path = (string) $this->argument('path');

        if ($path === '') {
            $this->error('A mount path is required.');

            return self::FAILURE;
        }

        $payload = [
            'description' => $this->option('description'),
            'config' => $this->parseKeyValueOptions((array) $this->option('config')),
            'options' => $this->parseKeyValueOptions((array) $this->option('option')),
        ];

        $payload = array_filter($payload, fn ($value) => $value !== null && $value !== []);

        $backendOptions = array_filter([
            'local' => (bool) $this->option('local'),
            'seal_wrap' => (bool) $this->option('seal-wrap'),
        ]);

        $secretsManager->enableSecretsEngine(
            (string) $this->option('type'),
            $path,
            $payload,
            $backendOptions,
            (string) $this->option('driver')
        );

        $this->info(sprintf(
            'Secrets engine [%s] mounted at [%s].',
            $this->option('type'),
            $path
        ));

        return self::SUCCESS;
    }

    /**
     * @param array<int, string> $options
     *
     * @return array<string, mixed>
     */
    protected function parseKeyValueOptions(array $options): array
    {
        $parsed = [];

        foreach ($options as $option) {
            if (! is_string($option) || ! str_contains($option, '=')) {
                continue;
            }

            [$key, $value] = array_map('trim', explode('=', $option, 2));

            if ($key === '') {
                continue;
            }

            $parsed[$key] = $this->castOptionValue($value);
        }

        return $parsed;
    }

    protected function castOptionValue(string $value): mixed
    {
        $unquoted = $this->stripWrappingQuotes($value);
        $normalized = strtolower($unquoted);

        return match ($normalized) {
            'true' => true,
            'false' => false,
            'null' => null,
            default => $this->castNumericValue($unquoted),
        };
    }

    protected function castNumericValue(string $value): mixed
    {
        if (is_numeric($value)) {
            return str_contains($value, '.') ? (float) $value : (int) $value;
        }

        return $value;
    }

    protected function stripWrappingQuotes(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return $value;
        }

        if (
            (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
            (str_starts_with($value, "'") && str_ends_with($value, "'"))
        ) {
            return substr($value, 1, -1);
        }

        return $value;
    }
}
