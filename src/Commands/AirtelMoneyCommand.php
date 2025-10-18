<?php

namespace Bmatovu\AirtelMoney\Commands;

use Illuminate\Console\Command;

class AirtelMoneyCommand extends Command
{
    public $signature = 'laravel-airtel-money';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
