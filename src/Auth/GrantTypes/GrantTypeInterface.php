<?php

namespace Bmatovu\AirtelMoney\Auth\GrantTypes;

interface GrantTypeInterface
{
    public function getToken($refreshToken = null): array;
}
