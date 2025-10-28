<?php

namespace Bmatovu\AirtelMoney;

use GuzzleHttp\ClientInterface;
use Illuminate\Container\Container;
use Illuminate\Contracts\Config\Repository;

class Authentication
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
    public function getToken(): array
    {
        $authUri = $this->config->get('airtel-money.token_uri');

        $response = $this->http->request('POST', $authUri, [
            'json' => [
                'client_id' => $this->config->get('airtel-money.client_id'),
                'client_secret' => $this->config->get('airtel-money.client_secret'),
                'grant_type' => 'client_credentials',
            ],
        ]);

        return json_decode((string) $response->getBody(), true);
    }
}
