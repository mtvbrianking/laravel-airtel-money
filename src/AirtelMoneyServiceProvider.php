<?php

namespace Bmatovu\AirtelMoney;

use Bmatovu\AirtelMoney\Commands\AuthCommand;
use Bmatovu\AirtelMoney\Commands\InitCommand;
use Bmatovu\AirtelMoney\Commands\PinCommand;
use Illuminate\Support\ServiceProvider;

class AirtelMoneyServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/airtel-money.php' => base_path('config/airtel-money.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/../storage/airtel.pub' => storage_path('airtel.pub'),
        ], 'public-key');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->commands([
            InitCommand::class,
            PinCommand::class,
            AuthCommand::class,
            // PurgeTokensCommand::class,
        ]);
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/airtel-money.php', 'airtel-money');
    }
}
