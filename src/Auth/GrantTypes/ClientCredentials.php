<?php

namespace Bmatovu\AirtelMoney\Auth\GrantTypes;

use GuzzleHttp\ClientInterface;

class ClientCredentials implements GrantTypeInterface
{
    private ClientInterface $client;

    private array $config;

    /**
     * @throws \InvalidArgumentException
     */
    public function __construct(ClientInterface $client, array $config)
    {
        $this->client = $client;

        $required = ['client_id', 'client_secret', 'token_uri'];

        if ($missing = array_diff($required, array_keys($config))) {
            $message = 'Parameters: '.implode(', ', $missing).' are required.';

            throw new \InvalidArgumentException($message, 0);
        }

        $this->config = array_merge([
            'scope' => '',
        ], $config);
    }

    public function getToken($refreshToken = null): array
    {
        $response = $this->client->request('POST', $this->config['token_uri'], [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic '.base64_encode($this->config['client_id'].':'.$this->config['client_secret']),
            ],
            'json' => [
                'grant_type' => 'client_credentials',
                'scope' => $this->config['scope'],
            ],
        ]);

        return json_decode($response->getBody(), true);
    }
}
