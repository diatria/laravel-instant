<?php

namespace Tests;

use Illuminate\Support\Facades\Route;

class ServiceProviderTest extends TestCase
{
    public function testRoutesAreDisabledByDefault(): void
    {
        $this->assertFalse(config('laravel-instant.route.enabled'));
        $this->assertSame('api', config('laravel-instant.route.prefix'));
        $this->assertFalse(Route::has('users'));
    }

    public function testPackageConfigIsLoaded(): void
    {
        $this->assertSame('int', config('laravel-instant.database.primary_key'));
        $this->assertSame(\Diatria\LaravelInstant\Utils\Permission::class, config('laravel-instant.class_permission'));
    }
}
