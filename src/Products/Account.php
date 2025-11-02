<?php

namespace Bmatovu\AirtelMoney\Products;

use GuzzleHttp\ClientInterface;
use Illuminate\Container\Container;
use Illuminate\Contracts\Config\Repository;

class Account
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
    public function getBalance(): array
    {
        $balanceUri = $this->config->get('airtel-money.account.balance_uri');

        $response = $this->http->request('GET', $balanceUri);

        return json_decode((string) $response->getBody(), true);
    }
}
