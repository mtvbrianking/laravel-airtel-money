<?php

namespace Bmatovu\AirtelMoney\Auth;

use Bmatovu\AirtelMoney\Auth\GrantTypes\GrantTypeInterface;
use Bmatovu\AirtelMoney\Auth\Repositories\TokenRepositoryInterface;
use Bmatovu\AirtelMoney\Support\Util;
use GuzzleHttp\Promise\RejectedPromise;
use Illuminate\Database\Eloquent\Model;
use Psr\Http\Message\RequestInterface;

class OAuth2Middleware
{
    protected ?Model $token = null;

    protected TokenRepositoryInterface $tokenRepository;

    protected GrantTypeInterface $grantType;

    protected ?GrantTypeInterface $refreshTokenGrantType;

    public function getToken(): ?Model
    {
        // If token is not set try to get it from the persistent storage.
        if ($this->token === null) {
            $this->token = $this->tokenRepository->retrieve();
        }

        // If storage token is not set or expired then try to acquire a new one...
        if ($this->token === null || Util::isExpired($this->token->expires_at)) {

            // Hydrate `rawToken` with a new access token
            $this->token = $this->requestNewToken();
        }

        return $this->token;
    }

    public function __construct(
        TokenRepositoryInterface $tokenRepository,
        GrantTypeInterface $grantType,
        ?GrantTypeInterface $refreshTokenGrantType = null
    ) {
        $this->tokenRepository = $tokenRepository;
        $this->grantType = $grantType;
        $this->refreshTokenGrantType = $refreshTokenGrantType;
    }

    public function __invoke(callable $handler): \Closure
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            if (! $request->hasHeader('Authorization')) {
                $request = $this->signRequest($request, $this->getToken());
            }

            return $handler($request, $options)->then(
                $this->onFulfilled($request, $options, $handler),
                $this->onRejected($request, $options, $handler)
            );
        };
    }

    private function onFulfilled(RequestInterface $request, array $options, callable $handler): \Closure
    {
        return function ($response) use ($request, $options, $handler) {
            // Only deal with Unauthorized response.
            if ($response && $response->getStatusCode() != 401) {
                return $response;
            }

            // If we already retried once, give up.
            if ($request->hasHeader('X-Guzzle-Retry')) {
                return $response;
            }

            // Delete the previous access token, if any
            $this->tokenRepository->delete($this->token->access_token);

            // Unset current token
            $this->token = null;

            // Acquire a new access token, and retry the request.
            $this->token = $this->getToken();

            $request = $request->withHeader('X-Guzzle-Retry', '1');

            $request = $this->signRequest($request, $this->token);

            return $handler($request, $options);
        };
    }

    private function onRejected(RequestInterface $request, array $options, callable $handler): \Closure
    {
        return function ($reason) {
            return new RejectedPromise($reason);
        };
    }

    protected function signRequest(RequestInterface $request, Model $token): RequestInterface
    {
        $authorization = $token->token_type.' '.$token->access_token;

        return $request->withHeader('Authorization', $authorization);
    }

    protected function requestNewToken(): Model
    {
        // Refresh an existing, but expired access token.
        if ($this->refreshTokenGrantType && $this->token && $this->token->refresh_token) {
            // Request new access token using the existing refresh token.
            $api_token = $this->refreshTokenGrantType->getToken($this->token->refresh_token);

            return $this->tokenRepository->create($api_token);
        }

        // Obtain new access token using the main grant type.
        $api_token = $this->grantType->getToken();

        return $this->tokenRepository->create($api_token);
    }
}
