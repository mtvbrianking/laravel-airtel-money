<?php

namespace Bmatovu\AirtelMoney\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Bmatovu\AirtelMoney\Products\Kyc
 */
class Kyc extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Bmatovu\AirtelMoney\Products\Kyc::class;
    }
}
