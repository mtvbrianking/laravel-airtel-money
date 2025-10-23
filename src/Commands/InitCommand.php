<?php

namespace Bmatovu\AirtelMoney\Commands;

use Bmatovu\AirtelMoney\Traits\CommandUtils;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;

class InitCommand extends Command
{
    use ConfirmableTrait, CommandUtils;

    protected $signature = "airtel-money:init
                            {--no-write : Don't write credentials to .env file.}
                            {--f|force : Force the operation to run when in production.}";

    public $description = 'Init command';

    public function handle(): int
    {
        if (! $this->confirmToProceed()) {
            return self::FAILURE;
        }

        $configs = $this->laravel['config']->get('airtel-money');

        $configs = $this->flattenArray($configs);

        foreach ($configs as $key => $value) {
            $this->writeConfig($key, $value);
        }

        return self::SUCCESS;
    }
}
