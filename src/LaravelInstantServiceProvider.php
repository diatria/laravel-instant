<?php

namespace Diatria\LaravelInstant;

use Illuminate\Support\ServiceProvider;
use Diatria\LaravelInstant\Console\Commands\MakeServiceCommand;
use Diatria\LaravelInstant\Console\Commands\MakeControllerCommand;

class LaravelInstantServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../publish/config/laravel-instant.php', 'laravel-instant');
    }

    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeControllerCommand::class,
                MakeServiceCommand::class
            ]);
        }

        $this->publishes([
            __DIR__ . '/../publish/models/' . config('laravel-instant.database.primary_key', 'int') . '/User.php' => app_path('Models/LaravelInstant/User.php'),
            __DIR__ . '/../publish/models/' . config('laravel-instant.database.primary_key', 'int') . '/Permission.php' => app_path('Models/LaravelInstant/Permission.php'),
            __DIR__ . '/../publish/models/' . config('laravel-instant.database.primary_key', 'int') . '/Role.php' => app_path('Models/LaravelInstant/Role.php'),
            __DIR__ . '/../publish/models/' . config('laravel-instant.database.primary_key', 'int') . '/RolePermission.php' => app_path('Models/LaravelInstant/RolePermission.php'),
        ], 'li-model');

        if (config('laravel-instant.database.migrations.enabled', false) && version_compare(app()->version(), '11.0', '<')) {
            $this->loadMigrationsFrom([
                __DIR__ . '/../publish/database/migrations/' . config('laravel-instant.database.primary_key', 'int'),
            ]);
        }

        if (config('laravel-instant.database.migrations.enabled', false) && version_compare(app()->version(), '11.0', '>=')) {
            $this->publishesMigrations([
                __DIR__ . '/../publish/database/migrations/' . config('laravel-instant.database.primary_key', 'int') => database_path('migrations'),
            ], 'li-migration');
        }

        $this->publishes([
            __DIR__ . '/../publish/database/seeders/PermissionSeeder.php' => database_path('seeders/PermissionSeeder.php'),
            __DIR__ . '/../publish/database/seeders/RolePermissionSeeder.php' => database_path('seeders/RolePermissionSeeder.php'),
            __DIR__ . '/../publish/database/seeders/RoleSeeder.php' => database_path('seeders/RoleSeeder.php')
        ], 'li-seeder');

        $this->publishes([
            __DIR__ . '/../publish/config/laravel-instant.php' => config_path('laravel-instant.php')
        ], 'li-config');

        $this->publishes([
            __DIR__ . '/../CLAUDE.md' => base_path('laravel-instant.md')
        ], 'li-docs');

        if (config('laravel-instant.route.enabled', false)) {
            $this->loadRoutesFrom(__DIR__ . "/Routes/api.php");
        }
    }
}
