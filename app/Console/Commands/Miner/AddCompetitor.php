<?php

namespace App\Console\Commands\Miner;

use App\eBay\TradingAPI;
use App\Miner\Competitor;
use DTS\eBaySDK\Trading\Enums\AckCodeType;
use DTS\eBaySDK\Trading\Types\GetUserRequestType;
use Illuminate\Console\Command;

class AddCompetitor extends Command
{
    protected $signature = 'competitor:add {username}';

    protected $description = 'Add Competitor to Miner';

    public function handle()
    {
        try {
            Competitor::add($this->argument('username'));
        } catch (\InvalidArgumentException $exception) {
            $this->error('Invalid username');

            return;
        }

        $this->info('Success!');
    }
}
