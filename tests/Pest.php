<?php

use Deepdigs\LaravelVaultSuite\Tests\TestCase;
use Mockery as M;

uses(TestCase::class)->in(__DIR__);

afterEach(function () {
    M::close();
});
