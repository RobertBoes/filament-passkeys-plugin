<?php

declare(strict_types=1);

namespace RobertBoes\FilamentPasskeys\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use RobertBoes\FilamentPasskeys\FilamentPasskeysServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            FilamentPasskeysServiceProvider::class,
        ];
    }
}
