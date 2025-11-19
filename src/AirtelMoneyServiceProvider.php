<?php

namespace Bmatovu\AirtelMoney;

use Bmatovu\AirtelMoney\Commands\AuthCommand;
use Bmatovu\AirtelMoney\Commands\PinCommand;
use Bmatovu\AirtelMoney\Products\Account;
use Bmatovu\AirtelMoney\Products\Authorization;
use Bmatovu\AirtelMoney\Products\Collection;
use Bmatovu\AirtelMoney\Products\Disbursement;
use Bmatovu\AirtelMoney\Products\Kyc;
use Bmatovu\AirtelMoney\Support\GuzzleHttpLogMiddleware;
use Bmatovu\AirtelMoney\Support\Util;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
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
            PinCommand::class,
            AuthCommand::class,
        ]);
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/airtel-money.php', 'airtel-money');

        $handlerStack = HandlerStack::create();

        $handlerStack->push(new GuzzleHttpLogMiddleware);

        $this->app->when(Authorization::class)
            ->needs(ClientInterface::class)
            ->give(function () use ($handlerStack) {
                return new Client([
                    'handler' => $handlerStack,
                    'base_uri' => $this->app['config']->get('airtel-money.base_uri'),
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ],
                ]);
            });

        $this->app->when([Kyc::class, Account::class, Collection::class, Disbursement::class])
            ->needs(ClientInterface::class)
            ->give(function () {
                return Util::http();
            });
    }
}
