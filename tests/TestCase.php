<?php

namespace Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app)
    {
        return [\Diatria\LaravelInstant\LaravelInstantServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('laravel-instant.route.enabled', false);
        $app['config']->set('laravel-instant.database.migrations.enabled', false);
        $app['config']->set('laravel-instant.auth.secret_key', 'testing-secret');
    }
}
