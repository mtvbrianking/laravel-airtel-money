<?php

namespace Bmatovu\AirtelMoney;

use Bmatovu\AirtelMoney\Commands\AirtelMoneyCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class AirtelMoneyServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-airtel-money')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel_airtel_money_table')
            ->hasCommand(AirtelMoneyCommand::class);
    }
}
