<?php

namespace Deepdigs\LaravelSecretsManager\Exceptions;

use Illuminate\Http\Client\Response;
use RuntimeException;

class SecretsManagerException extends RuntimeException
{
    /**
     * Create an exception for a failed HTTP request.
     */
    public static function requestFailed(Response $response, ?string $message = null): self
    {
        $payload = $response->json();
        $errors = [];

        if (is_array($payload) && array_key_exists('errors', $payload)) {
            $errors = (array) $payload['errors'];
        }

        $bodyMessage = $message ?? $response->body();

        if (! empty($errors)) {
            $bodyMessage = implode(', ', array_filter($errors)) ?: $bodyMessage;
        }

        return new self(sprintf(
            'Secrets backend request failed with HTTP %s: %s',
            $response->status(),
            $bodyMessage
        ), $response->status());
    }
}
