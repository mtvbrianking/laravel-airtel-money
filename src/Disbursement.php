<?php

namespace Bmatovu\AirtelMoney;

use Bmatovu\AirtelMoney\Support\Util;
use Illuminate\Container\Container;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Ramsey\Uuid\Uuid;

class Disbursement
{
    public ConfigRepository $config;

    public function __construct()
    {
        $this->config = Container::getInstance()->make(ConfigRepository::class);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws \GuzzleHttp\Exception\TransferException
     */
    public function send(string $phoneNumber, float $amount, ?string $id = null, ?string $reference = null): array
    {
        $phoneNumber = substr($phoneNumber, -9);

        $paymentUri = $this->config->get('airtel-money.disbursement.payment_uri');

        $response = Util::http()->request('POST', $paymentUri, [
            'json' => [
                'payee' => [
                    'msisdn' => $phoneNumber,
                ],
                'reference' => $reference ?? 'Disbursement',
                'pin' => $this->config->get('airtel-money.encrypted_pin'),
                'transaction' => [
                    'amount' => $amount,
                    'id' => $id ?? Uuid::uuid4()->toString(),
                ],
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws \GuzzleHttp\Exception\TransferException
     */
    public function getTransaction(string $transactionId): array
    {
        $transactionUri = $this->config->get('airtel-money.disbursement.transaction_inquiry_uri');

        $transactionUri = str_replace(':transactionId', $transactionId, $transactionUri);

        $response = Util::http()->request('GET', $transactionUri);

        return json_decode($response->getBody(), true);
    }
}
