<?php

use Deepdigs\LaravelSecretsManager\Tests\TestCase;
use Mockery as M;

uses(TestCase::class)->in(__DIR__);

afterEach(function () {
    M::close();
});
