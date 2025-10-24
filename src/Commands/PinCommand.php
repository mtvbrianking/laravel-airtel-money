<?php

namespace Bmatovu\AirtelMoney\Commands;

use Bmatovu\AirtelMoney\Traits\CommandUtils;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;

class PinCommand extends Command
{
    use CommandUtils, ConfirmableTrait;

    protected $signature = "airtel-money:pin
                            {--no-write : Don't write credentials to .env file.}
                            {--f|force : Force the operation to run when in production.}";

    public $description = 'Encrypt the disbursement PIN';

    public function handle(): int
    {
        if (! $this->confirmToProceed()) {
            return self::FAILURE;
        }

        $pin = $this->ask('Enter PIN');

        $publicKeyPath = (string) $this->writeConfig('public_key');

        $this->info('Using: '.storage_path($publicKeyPath));

        $encryptedPin = $this->encrypt($pin, storage_path($publicKeyPath));

        $this->persistConfig('airtel-money.encrypted_pin', $encryptedPin);

        $this->info('Encrypted PIN written to .env file');

        return self::SUCCESS;
    }

    public function encrypt(string $data, string $publicKeyPath, int $padding = OPENSSL_PKCS1_OAEP_PADDING): string
    {
        $publicKey = file_get_contents($publicKeyPath);
        if ($publicKey === false) {
            throw new \RuntimeException("Failed to read public key from {$publicKeyPath}");
        }

        if (! openssl_public_encrypt($data, $encrypted, $publicKey, $padding)) {
            throw new \RuntimeException('Failed to encrypt data with the public key.');
        }

        return base64_encode($encrypted);
    }
}
