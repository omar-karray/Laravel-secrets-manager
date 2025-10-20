<?php

namespace Deepdigs\LaravelSecretsManager\Drivers\Vault;

use Deepdigs\LaravelSecretsManager\Contracts\SecretsDriver;
use Deepdigs\LaravelSecretsManager\Exceptions\SecretsManagerException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;

class VaultSecretsDriver implements SecretsDriver
{
    /**
     * @var array<string, mixed>
     */
    protected array $config;

    protected PendingRequest $http;

    public function __construct(HttpFactory $http, array $config)
    {
        $this->config = $config;
        $this->http = $this->buildHttpClient($http, $config);
    }

    public function read(string $path, array $options = []): array
    {
        $endpoint = $this->buildSecretsEndpoint($path, $options, 'read');

        $response = $this->http->get($endpoint);

        $this->throwIfFailed($response);

        if ($this->isKvV2($options)) {
            return (array) $response->json('data.data', []);
        }

        return (array) $response->json('data', []);
    }

    public function write(string $path, array $payload, array $options = []): array
    {
        $endpoint = $this->buildSecretsEndpoint($path, $options, 'write');

        if ($this->isKvV2($options)) {
            $payload = ['data' => $payload];
        }

        $response = $this->http->post($endpoint, $payload);

        $this->throwIfFailed($response);

        return (array) $response->json('data', []);
    }

    public function delete(string $path, array $options = []): void
    {
        $endpoint = $this->buildSecretsEndpoint($path, $options, 'delete');

        $response = $this->http->delete($endpoint);

        $this->throwIfFailed($response);
    }

    public function list(string $path, array $options = []): array
    {
        $endpoint = $this->buildSecretsEndpoint($path, $options, 'list');

        $response = $this->http
            ->withHeaders(['X-Vault-Request' => 'true'])
            ->send('LIST', $endpoint);

        $this->throwIfFailed($response);

        return (array) $response->json('data.keys', []);
    }

    public function sealStatus(array $options = []): array
    {
        $response = $this->http->get('/v1/sys/seal-status');

        $this->throwIfFailed($response);

        return (array) $response->json();
    }

    public function submitUnsealKey(string $key, array $options = []): array
    {
        $response = $this->http->post('/v1/sys/unseal', array_merge([
            'key' => $key,
        ], Arr::only($options, ['reset', 'migrate'])));

        $this->throwIfFailed($response);

        return (array) $response->json();
    }

    public function enableSecretsEngine(string $type, string $path, array $settings = [], array $options = []): array
    {
        $payload = array_merge([
            'type' => $type,
            'config' => Arr::get($settings, 'config', []),
            'options' => Arr::get($settings, 'options', []),
            'description' => Arr::get($settings, 'description'),
        ], Arr::only($options, ['local', 'seal_wrap']));

        $payload = array_filter($payload, fn ($value) => $value !== null && $value !== []);

        $response = $this->http->post('/v1/sys/mounts/'.trim($path, '/'), $payload);

        $this->throwIfFailed($response);

        return (array) $response->json();
    }

    protected function buildSecretsEndpoint(string $path, array $options, string $operation): string
    {
        $mount = trim(Arr::get($options, 'mount', Arr::get($this->config, 'engine.mount', 'secret')), '/');
        $path = trim($path, '/');
        $version = (int) Arr::get($options, 'version', Arr::get($this->config, 'engine.version', 2));

        return match ($operation) {
            'read', 'write', 'delete' => $version === 2
                ? "/v1/{$mount}/data/{$path}"
                : "/v1/{$mount}/{$path}",
            'list' => $version === 2
                ? "/v1/{$mount}/metadata/{$path}"
                : "/v1/{$mount}/{$path}",
            default => throw new SecretsManagerException("Unsupported secrets operation [{$operation}]"),
        };
    }

    protected function isKvV2(array $options): bool
    {
        return (int) Arr::get($options, 'version', Arr::get($this->config, 'engine.version', 2)) === 2;
    }

    protected function buildHttpClient(HttpFactory $http, array $config): PendingRequest
    {
        $address = rtrim((string) Arr::get($config, 'address', 'http://127.0.0.1:8200'), '/');

        $client = $http->baseUrl($address);

        $verify = Arr::get($config, 'verify', true);
        $timeout = Arr::get($config, 'timeout');
        $token = Arr::get($config, 'token');
        $headers = [];

        if (Arr::has($config, 'namespace')) {
            $headers['X-Vault-Namespace'] = $config['namespace'];
        }

        if (Arr::has($config, 'headers')) {
            $headers = array_merge($headers, (array) $config['headers']);
        }

        if ($token) {
            $headers['X-Vault-Token'] = $token;
        }

        if (! empty($headers)) {
            $client = $client->withHeaders($headers);
        }

        if (! $verify) {
            $client = $client->withoutVerifying();
        }

        if ($timeout) {
            $client = $client->timeout((int) $timeout);
        }

        if ($caCert = Arr::get($config, 'ca_cert')) {
            $client = $client->withOptions(['verify' => $caCert]);
        }

        if ($clientCert = Arr::get($config, 'client_cert')) {
            $client = $client->withOptions(['cert' => $clientCert]);
        }

        if ($clientKey = Arr::get($config, 'client_key')) {
            $client = $client->withOptions(['ssl_key' => $clientKey]);
        }

        return $client;
    }

    protected function throwIfFailed(Response $response): void
    {
        if ($response->successful()) {
            return;
        }

        throw SecretsManagerException::requestFailed($response);
    }
}
