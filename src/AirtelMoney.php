<?php

namespace Bmatovu\AirtelMoney;

use Bmatovu\AirtelMoney\Support\Util;
use GuzzleHttp\Client;
use Illuminate\Container\Container;
use Illuminate\Contracts\Config\Repository as ConfigRepository;

class AirtelMoney
{
    public ConfigRepository $config;

    public function __construct()
    {
        $this->config = Container::getInstance()->make(ConfigRepository::class);
    }

    /**
     * @throws GuzzleHttp\Exception\TransferException
     */
    public function getToken(): array
    {
        $authUri = $this->config->get('airtel-money.token_uri');

        $client = new Client([
            'handler' => Util::logMiddleware(),
            'base_uri' => $this->config->get('airtel-money.base_uri'),
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);

        $response = $client->post($authUri, [
            'json' => [
                'client_id' => $this->config->get('airtel-money.client_id'),
                'client_secret' => $this->config->get('airtel-money.client_secret'),
                'grant_type' => 'client_credentials',
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * @throws GuzzleHttp\Exception\TransferException
     */
    public function getUser(string $phoneNumber): array
    {
        $phoneNumber = substr($phoneNumber, -9);

        $kycUri = $this->config->get('airtel-money.kyc_uri');

        $kycUri = str_replace(':phoneNumber', $phoneNumber, $kycUri);

        $response = Util::http()->request('GET', $kycUri);

        return json_decode($response->getBody(), true);
    }
}
