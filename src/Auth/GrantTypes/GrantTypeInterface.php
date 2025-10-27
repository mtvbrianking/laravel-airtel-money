<?php

namespace Bmatovu\AirtelMoney\Auth\GrantTypes;

interface GrantTypeInterface
{
    public function getToken(?string $refreshToken = null): array;
}
