<?php
namespace Diatria\LaravelInstant;

use Illuminate\Support\ServiceProvider;
use Diatria\LaravelInstant\Console\Commands\MakeServiceCommand;
use Diatria\LaravelInstant\Console\Commands\MakeControllerCommand;

class LaravelInstantServiceProvider extends ServiceProvider
{
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
            __DIR__ . '/../publish/models/' . config('laravel-instant.database.primary_key', 'int') . '/User.php' => app_path('Models/User.php'),
            __DIR__ . '/../publish/models/' . config('laravel-instant.database.primary_key', 'int') . '/Permission.php' => app_path('Models/Permission.php'),
            __DIR__ . '/../publish/models/' . config('laravel-instant.database.primary_key', 'int') . '/Role.php' => app_path('Models/Role.php'),
            __DIR__ . '/../publish/models/' . config('laravel-instant.database.primary_key', 'int') . '/RolePermission.php' => app_path('Models/RolePermission.php'),
        ], 'li-model');

        if (app()->version() <= 10) {
            $this->loadMigrationsFrom([
                __DIR__ . '/../publish/database/migrations/' . config('laravel-instant.database.primary_key', 'int'),
            ]);
        }

        if (app()->version() >= 11) {
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

        $this->loadRoutesFrom(__DIR__ . "/Routes/api.php");
    }
}
