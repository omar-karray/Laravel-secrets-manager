<?php

namespace Deepdigs\LaravelVaultSuite\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Deepdigs\LaravelVaultSuite\LaravelVaultSuite
 */
class LaravelVaultSuite extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Deepdigs\LaravelVaultSuite\LaravelVaultSuite::class;
    }
}
