<?php

namespace Deepdigs\LaravelSecretsManager\Contracts;

interface SecretsDriver
{
    /**
     * Retrieve secret data stored at the given path.
     *
     * @return array<string, mixed>
     */
    /**
     * @param array<string, mixed> $options
     */
    public function read(string $path, array $options = []): array;

    /**
     * Write or update secret data at the given path.
     *
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $options
     */
    public function write(string $path, array $payload, array $options = []): array;

    /**
     * Delete a secret at the given path.
     */
    /**
     * @param array<string, mixed> $options
     */
    public function delete(string $path, array $options = []): void;

    /**
     * List secrets beneath the given path.
     *
     * @return array<int, string>
     */
    /**
     * @param array<string, mixed> $options
     */
    public function list(string $path, array $options = []): array;

    /**
     * Return the current seal status for the backend.
     *
     * @return array<string, mixed>
     */
    /**
     * @param array<string, mixed> $options
     */
    public function sealStatus(array $options = []): array;

    /**
     * Submit a single unseal key share to the backend.
     *
     * @return array<string, mixed>
     */
    /**
     * @param array<string, mixed> $options
     */
    public function submitUnsealKey(string $key, array $options = []): array;

    /**
     * Enable or configure a secrets engine at the given mount path.
     *
     * @param array<string, mixed> $settings
     *
     * @return array<string, mixed>
     */
    /**
     * @param array<string, mixed> $settings
     * @param array<string, mixed> $options
     */
    public function enableSecretsEngine(string $type, string $path, array $settings = [], array $options = []): array;
}
