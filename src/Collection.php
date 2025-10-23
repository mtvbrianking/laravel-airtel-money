<?php

namespace Bmatovu\AirtelMoney;

use Bmatovu\AirtelMoney\Support\Util;
use Illuminate\Container\Container;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Ramsey\Uuid\Uuid;

class Collection
{
    public ConfigRepository $config;

    public function __construct()
    {
        $this->config = Container::getInstance()->make(ConfigRepository::class);
    }

    public function receive(string $phoneNumber, float $amount, ?string $id = null, ?string $reference = null): array
    {
        $phoneNumber = substr($phoneNumber, -9);

        $paymentUri = $this->config->get('airtel-money.collection.payment_uri');

        $response = Util::http()->request('GET', $paymentUri, [
            'json' => [
                'reference' => $reference ?? 'Collection',
                'subscriber' => [
                    'country' => $this->config->get('airtel-money.country'),
                    'currency' => $this->config->get('airtel-money.currency'),
                    'phoneNumber' => $phoneNumber,
                ],
                'transaction' => [
                    'amount' => $amount,
                    'country' => $this->config->get('airtel-money.country'),
                    'currency' => $this->config->get('airtel-money.currency'),
                    'id' => $id ?? Uuid::uuid4()->toString(),
                ],
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    public function refund(string $transactionId): array
    {
        $refundUri = $this->config->get('airtel-money.collection.refund_uri');

        $refundUri = str_replace(':transactionId', $transactionId, $refundUri);

        $response = Util::http()->request('GET', $refundUri, [
            'json' => [
                'transaction' => [
                    'airtel_money_id' => $transactionId,
                ],
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    public function getTransaction(string $transactionId): array
    {
        $transactionUri = $this->config->get('airtel-money.collection.transaction_inquiry_uri');

        $transactionUri = str_replace(':transactionId', $transactionId, $transactionUri);

        $response = Util::http()->request('GET', $transactionUri);

        return json_decode($response->getBody(), true);
    }

    public function getBalance(): array
    {
        $balanceUri = $this->config->get('airtel-money.collection.balance_inquiry_uri');

        $response = Util::http()->request('GET', $balanceUri);

        return json_decode($response->getBody(), true);
    }
}
