<?php

namespace Bmatovu\AirtelMoney\Auth\Models;

interface TokenInterface
{
    public function getAccessToken(): string;

    public function getRefreshToken(): ?string;

    public function getTokenType(): string;

    public function getExpiresAt(): \DateTime;

    public function isExpired(): bool;
}
