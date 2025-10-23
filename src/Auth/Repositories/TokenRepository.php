<?php

namespace Bmatovu\AirtelMoney\Auth\Repositories;

use Bmatovu\AirtelMoney\Models\Token;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TokenRepository implements TokenRepositoryInterface
{
    public function create(array $tokenAttrs): Model
    {
        $tokenAttrs['token_type'] = 'Bearer';

        if (isset($tokenAttrs['expires_in'])) {
            $tokenAttrs['expires_at'] = Carbon::now()->addSeconds($tokenAttrs['expires_in']);
        }

        return Token::create($tokenAttrs);
    }

    public function retrieveAll()
    {
        return Token::get();
    }

    public function retrieve(?string $accessToken = null): ?Model
    {
        if ($accessToken) {
            return Token::where('access_token', $accessToken)->first();
        }

        return Token::latest('created_at')->first();
    }

    public function update(string $accessToken, array $tokenAttrs): Model
    {
        $token = Token::where('access_token', $accessToken)->firstOrFail();

        $token->update($tokenAttrs);

        return $token->fresh();
    }

    public function delete(string $accessToken): void
    {
        Token::where('access_token', $accessToken)->delete();
    }
}
