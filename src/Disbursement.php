<?php

namespace Bmatovu\AirtelMoney;

use GuzzleHttp\ClientInterface;
use Illuminate\Container\Container;
use Illuminate\Contracts\Config\Repository;
use Ramsey\Uuid\Uuid;

class Disbursement
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
    public function send(string $phoneNumber, float $amount, ?string $id = null, ?string $reference = null): array
    {
        $phoneNumber = substr($phoneNumber, -9);

        $paymentUri = $this->config->get('airtel-money.disbursement.payment_uri');

        $response = $this->http->request('POST', $paymentUri, [
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

        return json_decode((string) $response->getBody(), true);
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

        $response = $this->http->request('GET', $transactionUri);

        return json_decode((string) $response->getBody(), true);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws \GuzzleHttp\Exception\TransferException
     */
    public function getUser(string $phoneNumber): array
    {
        $phoneNumber = substr($phoneNumber, -9);

        $kycUri = $this->config->get('airtel-money.kyc_uri');

        $kycUri = str_replace(':phoneNumber', $phoneNumber, $kycUri);

        $response = $this->http->request('GET', $kycUri);

        return json_decode((string) $response->getBody(), true);
    }
}
