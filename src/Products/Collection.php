<?php

namespace Bmatovu\AirtelMoney\Products;

use GuzzleHttp\ClientInterface;
use Illuminate\Container\Container;
use Illuminate\Contracts\Config\Repository;
use Ramsey\Uuid\Uuid;

class Collection
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
    public function receive(string $phoneNumber, float $amount, ?string $id = null, ?string $reference = null): array
    {
        $phoneNumber = substr($phoneNumber, -9);

        $paymentUri = $this->config->get('airtel-money.collection.payment_uri');

        $response = $this->http->request('POST', $paymentUri, [
            'json' => [
                'reference' => $reference ?? 'Collection',
                'subscriber' => [
                    'country' => $this->config->get('airtel-money.country'),
                    'currency' => $this->config->get('airtel-money.currency'),
                    'msisdn' => $phoneNumber,
                ],
                'transaction' => [
                    'amount' => $amount,
                    'country' => $this->config->get('airtel-money.country'),
                    'currency' => $this->config->get('airtel-money.currency'),
                    'id' => $id ?? Uuid::uuid4()->toString(),
                ],
            ],
        ]);

        return json_decode((string) $response->getBody(), true);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws \GuzzleHttp\Exception\TransferException
     */
    public function refund(string $airtelMoneyId): array
    {
        $refundUri = $this->config->get('airtel-money.collection.refund_uri');

        $response = $this->http->request('POST', $refundUri, [
            'json' => [
                'transaction' => [
                    'airtel_money_id' => $airtelMoneyId,
                ],
            ],
        ]);

        return json_decode((string) $response->getBody(), true);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws \GuzzleHttp\Exception\TransferException
     */
    public function getTransaction(string $transactionId): array
    {
        $transactionUri = $this->config->get('airtel-money.collection.transaction_uri');

        $transactionUri = str_replace(':transactionId', $transactionId, $transactionUri);

        $response = $this->http->request('GET', $transactionUri);

        return json_decode((string) $response->getBody(), true);
    }
}
