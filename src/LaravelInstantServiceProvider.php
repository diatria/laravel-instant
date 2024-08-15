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

        $this->loadRoutesFrom(__DIR__ . "/Routes/api.php");
    }
}
