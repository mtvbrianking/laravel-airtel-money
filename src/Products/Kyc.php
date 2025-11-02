<?php

namespace Bmatovu\AirtelMoney\Products;

use GuzzleHttp\ClientInterface;
use Illuminate\Container\Container;
use Illuminate\Contracts\Config\Repository;

class Kyc
{
    protected ClientInterface $http;

    protected Repository $config;

    public function __construct(ClientInterface $http)
    {
        $this->http = $http;
        $this->config = Container::getInstance()->make('config');
    }

    /**
     * @return array<string, mixed>
     *
     * @throws \GuzzleHttp\Exception\TransferException
     */
    public function getUser(string $phoneNumber): array
    {
        $phoneNumber = substr($phoneNumber, -9);

        $userUri = $this->config->get('airtel-money.kyc.user_uri');

        $userUri = str_replace(':phoneNumber', $phoneNumber, $userUri);

        $response = $this->http->request('GET', $userUri);

        return json_decode((string) $response->getBody(), true);
    }
}
