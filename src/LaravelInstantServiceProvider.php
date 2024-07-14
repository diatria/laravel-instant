<?php
namespace Diatria\LaravelInstant;

use Diatria\LaravelInstant\Console\Commands\MakeServiceCommand;
use Illuminate\Support\ServiceProvider;

class LaravelInstantServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([MakeServiceCommand::class]);
        }

        $this->loadRoutesFrom(__DIR__ . "/Routes/api.php");
    }
}
