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
                        'Authorization' => 'Basic '.base64_encode('67b4pyJmfw3g4KDF:efcf410d-45f7-4a3c-9591-584ec55daaed'),
                        'Content-Type' => 'application/json',
                        'X-Custom-Header' => ['value1', 'value2'],
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
