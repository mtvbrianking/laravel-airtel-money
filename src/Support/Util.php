<?php

namespace Bmatovu\AirtelMoney\Support;

use Bmatovu\AirtelMoney\Auth\GrantTypes\ClientCredentials;
use Bmatovu\AirtelMoney\Auth\OAuth2Middleware;
use Bmatovu\AirtelMoney\Auth\Repositories\TokenRepository;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Illuminate\Container\Container;
use Illuminate\Support\Str;
use Psr\Log\LogLevel;

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

    public static function logMiddleware(?HandlerStack $handlerStack = null): HandlerStack
    {
        $handlerStack = $handlerStack ?? HandlerStack::create();

        $id = $_SERVER['REQUEST_ID'] ?? Str::random(10);

        $messageFormats = [
            "HTTP_OUT_{$id} [Request] {method} {target}" => LogLevel::INFO,
            "HTTP_OUT_{$id} [Request] [Headers] \n{req_headers}" => LogLevel::DEBUG,
            "HTTP_OUT_{$id} [Request] [Body] {req_body}" => LogLevel::DEBUG,
            "HTTP_OUT_{$id} [Response] HTTP/{version} {code} {phrase} Size: {res_header_Content-Length}" => LogLevel::INFO,
            "HTTP_OUT_{$id} [Response] [Headers] \n{res_headers}" => LogLevel::DEBUG,
            "HTTP_OUT_{$id} [Response] [Body] {res_body}" => LogLevel::DEBUG,
            // "HTTP_OUT_{$id} [Error] {error}" => LogLevel::ERROR,
        ];

        $logger = Container::getInstance()->get('log');

        foreach ($messageFormats as $format => $level) {
            $messageFormatter = new MessageFormatter($format);
            $logMiddleware = Middleware::log($logger, $messageFormatter, $level);
            $handlerStack->unshift($logMiddleware);
        }

        return $handlerStack;
    }

    public static function http(): ClientInterface
    {
        $config = Container::getInstance()->make('config');

        $handlerStack = HandlerStack::create();

        $handlerStack = Util::logMiddleware($handlerStack);

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

        $handlerStack = Util::logMiddleware();

        $options = array_merge([
            'base_uri' => $config->get('airtel-money.base_uri'),
            'handler' => $handlerStack,
        ], $config->get('airtel-money.guzzle.options'));

        $client = new Client($options);

        $config = [
            'client_id' => $config->get('airtel-money.client_id'),
            'client_secret' => $config->get('airtel-money.client_secret'),
            'token_uri' => $config->get('airtel-money.token_uri'),
        ];

        $clientCredGrant = new ClientCredentials($client, $config);

        $tokenRepo = new TokenRepository;

        return new OAuth2Middleware($clientCredGrant, null, $tokenRepo);
    }
}
