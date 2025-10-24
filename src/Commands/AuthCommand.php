<?php

namespace Bmatovu\AirtelMoney\Commands;

use Bmatovu\AirtelMoney\Support\Util;
use Bmatovu\AirtelMoney\Traits\CommandUtils;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;

class AuthCommand extends Command
{
    use CommandUtils, ConfirmableTrait;

    protected $signature = "airtel-money:auth
                            {--no-write : Don't write credentials to .env file.}
                            {--f|force : Force the operation to run when in production.}";

    public $description = 'Setup authentication';

    public function handle(): int
    {
        if (! $this->confirmToProceed()) {
            return self::FAILURE;
        }

        $clientId = $this->writeConfig('client_id');

        $clientSecret = $this->writeConfig('client_secret');

        $token = $this->auth($clientId, $clientSecret);

        echo "\n".json_encode($token).PHP_EOL;

        $phoneNumber = $this->ask('Enter Phone Number');

        $accessToken = $token['access_token'];

        $user = $this->kyc($accessToken, $phoneNumber);

        echo "\n".json_encode($user).PHP_EOL;

        return self::SUCCESS;
    }

    protected function auth(string $clientId, string $clientSecret): array
    {
        $authUri = $this->laravel['config']->get('airtel-money.token_uri');

        try {
            $response = $this->http()->request('POST', $authUri, [
                'json' => [
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'grant_type' => 'client_credentials',
                ],
            ]);
        } catch (RequestException $ex) {
            $response = $ex->getResponse();
            $this->error("\n".$response->getStatusCode().' '.$response->getReasonPhrase());
        }

        return json_decode($response->getBody(), true);
    }

    protected function kyc(string $accessToken, string $phoneNumber): array
    {
        $phoneNumber = substr($phoneNumber, -9);

        $kycUri = $this->laravel['config']->get('airtel-money.kyc_uri');

        $kycUri = str_replace(':phoneNumber', $phoneNumber, $kycUri);

        try {
            $response = $this->http()->request('GET', $kycUri, [
                'headers' => [
                    'X-Country' => $this->laravel['config']->get('airtel-money.country'),
                    'X-Currency' => $this->laravel['config']->get('airtel-money.currency'),
                    'Authorization' => "Bearer {$accessToken}",
                ],
            ]);
        } catch (RequestException $ex) {
            $response = $ex->getResponse();
            $this->error("\nHTTP ".$response->getStatusCode().' '.$response->getReasonPhrase());
        }

        return json_decode($response->getBody(), true);
    }

    protected function http(): ClientInterface
    {
        $handlerStack = Util::logMiddleware();

        $options = array_merge([
            'handler' => $handlerStack,
            'progress' => function () {
                echo '.';
            },
            'base_uri' => $this->laravel['config']->get('airtel-money.base_uri'),
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ], (array) $this->laravel['config']->get('airtel-money.guzzle.options'));

        return new Client($options);
    }
}
