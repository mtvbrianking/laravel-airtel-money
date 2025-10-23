<?php

namespace Bmatovu\AirtelMoney\Auth\Repositories;

use Illuminate\Database\Eloquent\Model;

interface TokenRepositoryInterface
{
    public function create(array $TokenAttrs): Model;

    public function retrieve(?string $accessToken = null): ?Model; // firstOrNull

    public function update(string $accessToken, array $tokenAttrs): Model;

    public function delete(string $accessToken): void;
}
