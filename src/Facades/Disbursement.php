<?php

namespace Bmatovu\AirtelMoney\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Bmatovu\AirtelMoney\Disbursement
 */
class Disbursement extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Bmatovu\AirtelMoney\Disbursement::class;
    }
}
