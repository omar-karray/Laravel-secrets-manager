<?php

namespace Deepdigs\LaravelSecretsManager\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Deepdigs\LaravelSecretsManager\LaravelSecretsManager
 */
class LaravelSecretsManager extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Deepdigs\LaravelSecretsManager\LaravelSecretsManager::class;
    }
}
