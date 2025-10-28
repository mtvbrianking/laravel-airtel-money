<?php

namespace Bmatovu\AirtelMoney;

use Bmatovu\AirtelMoney\Commands\AuthCommand;
use Bmatovu\AirtelMoney\Commands\InitCommand;
use Bmatovu\AirtelMoney\Commands\PinCommand;
use Bmatovu\AirtelMoney\Support\Util;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Illuminate\Support\ServiceProvider;

class AirtelMoneyServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
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

        $this->app->when(Authentication::class)
            ->needs(ClientInterface::class)
            ->give(function () {
                return new Client([
                    'base_uri' => config('airtel-money.base_uri'),
                    'handler' => Util::logMiddleware(),
                    'base_uri' => $this->app['config']->get('airtel-money.base_uri'),
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ],
                ]);
            });

        $this->app->when([Collection::class, Disbursement::class])
            ->needs(ClientInterface::class)
            ->give(function () {
                return Util::http();
            });

        // $this->app->bind('airtel-money.no_auth_client', function ($app) {
        //     return new Client([
        //         'base_uri' => config('airtel-money.base_uri'),
        //         'handler' => Util::logMiddleware(),
        //         'base_uri' => $this->app['config']->get('airtel-money.base_uri'),
        //         'headers' => [
        //             'Content-Type' => 'application/json',
        //         ],
        //     ]);
        // });

        // $this->app->bind('airtel-money.no_auth_client', function ($app) {
        //     return Util::http();
        // });
    }
}
