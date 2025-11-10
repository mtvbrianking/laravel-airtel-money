<?php

namespace Bmatovu\AirtelMoney\Support;

use Bmatovu\AirtelMoney\Auth\GrantTypes\ClientCredentials;
use Bmatovu\AirtelMoney\Auth\OAuth2Middleware;
use Bmatovu\AirtelMoney\Auth\Repositories\TokenRepository;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use Illuminate\Container\Container;

class Util
{
    public static function isExpired(\DateTimeInterface|Carbon|string|null $expires_at): bool
    {
        if (is_null($expires_at)) {
            return false;
        }

        if ($expires_at instanceof Carbon) {
            return $expires_at->isPast();
        }

        $now = new \DateTime;

        if ($expires_at instanceof \DateTimeInterface) {
            return $now > $expires_at;
        }

        $expires_at = \DateTime::createFromFormat('Y-m-d H:i:s', $expires_at);

        return $now > $expires_at;
    }

    public static function http(): ClientInterface
    {
        $config = Container::getInstance()->make('config');

        $handlerStack = HandlerStack::create();

        $handlerStack->push(new GuzzleHttpLogMiddleware);

        $handlerStack->unshift(self::getAuthBroker());

        $options = array_merge([
            'handler' => $handlerStack,
            'base_uri' => $config->get('airtel-money.base_uri'),
            'headers' => [
                // 'Authorization' => 'Bearer *********',
                'Content-Type' => 'application/json',
                'X-Country' => $config->get('airtel-money.country'),
                'X-Currency' => $config->get('airtel-money.currency'),
            ],
        ], (array) $config->get('airtel-money.guzzle.options'));

        return new Client($options);
    }

    public static function getAuthBroker(): OAuth2Middleware
    {
        $config = Container::getInstance()->make('config');

        $handlerStack = HandlerStack::create();

        $handlerStack->push(new GuzzleHttpLogMiddleware);

        $options = array_merge([
            'base_uri' => $config->get('airtel-money.base_uri'),
            'handler' => $handlerStack,
        ], $config->get('airtel-money.guzzle.options'));

        $client = new Client($options);

        $config = [
            'client_id' => $config->get('airtel-money.client_id'),
            'client_secret' => $config->get('airtel-money.client_secret'),
            'token_uri' => $config->get('airtel-money.authorization.token_uri'),
        ];

        $clientCredGrant = new ClientCredentials($client, $config);

        $tokenRepo = new TokenRepository;

        return new OAuth2Middleware($clientCredGrant, null, $tokenRepo);
    }
}
