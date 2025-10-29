<?php

namespace Bmatovu\AirtelMoney\Commands;

use Bmatovu\AirtelMoney\Facades\Authentication;
use Bmatovu\AirtelMoney\Traits\CommandUtils;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Psr\Http\Message\ResponseInterface;

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

        $this->line('<options=bold>Client App Credentials</>');

        $this->writeConfig('client_id');

        $this->writeConfig('client_secret');

        try {
            $apiRes = Authentication::getToken();

            $this->line(json_encode($apiRes, JSON_PRETTY_PRINT));
        } catch (RequestException $ex) {
            $response = $ex->getResponse();

            if ($response instanceof ResponseInterface) {
                $this->error($response->getStatusCode().' '.$response->getReasonPhrase());

                $this->error($response->getBody());

                return self::FAILURE;
            }

            $this->error($ex->getMessage());
        }

        return self::SUCCESS;
    }
}
