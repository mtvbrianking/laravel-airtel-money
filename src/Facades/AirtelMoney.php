<?php

namespace Bmatovu\AirtelMoney\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Bmatovu\AirtelMoney\AirtelMoney
 */
class AirtelMoney extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Bmatovu\AirtelMoney\AirtelMoney::class;
    }
}
