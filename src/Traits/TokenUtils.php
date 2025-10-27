<?php

namespace Bmatovu\AirtelMoney\Traits;

trait TokenUtils
{
    public function getAccessToken(): string
    {
        return $this->access_token;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refresh_token;
    }

    public function getTokenType(): string
    {
        return $this->token_type;
    }

    // public function getExpiresAt(): string|\Datetime
    public function getExpiresAt(): \Datetime
    {
        return $this->expires_at;
    }

    public function isExpired(): bool
    {
        if (is_null($this->expires_at)) {
            return false;
        }

        return $this->expires_at->isPast();
    }
}
