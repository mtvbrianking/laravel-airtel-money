<?php

namespace Bmatovu\AirtelMoney\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Bmatovu\AirtelMoney\Collection
 */
class Collection extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Bmatovu\AirtelMoney\Collection::class;
    }
}
