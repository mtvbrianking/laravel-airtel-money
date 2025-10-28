<?php

namespace Bmatovu\AirtelMoney\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Bmatovu\AirtelMoney\Authentication
 */
class Authentication extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Bmatovu\AirtelMoney\Authentication::class;
    }
}
