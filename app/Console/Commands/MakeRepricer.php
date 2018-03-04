<?php

namespace App\Console\Commands;

use App\Item;
use Illuminate\Console\Command;

class MakeRepricer extends Command
{
    protected $signature = 'repricer:make {item_id}';

    protected $description = 'Make New Repricer for eBay Item (already saved in database)';

    public function handle()
    {
        $item = Item::find($this->argument('item_id'));

        // Questionare
        $profit        = (double)$this->ask('Which is profit?');
        $sourceTax     = (double)$this->ask('Which is source tax? (default: 9%)', 0.09);
        $finalValueFee = (double)$this->ask('Final Value Fee? (default: 9.15%)', 0.0915);
        $paypalRate    = (double)$this->ask('PayPal Rate by Percents (enter double, default: 3.9%)?', 0.039);
        $paypalRateUsd = (double)$this->ask('PayPal Rate by USD (default $0.3)?', 0.3);
        $minimumPrice  = (double)$this->ask('Minimum Price (default: $0.0)', 0.0);
        // End Questionare

        $repricer = $item->repricer()->create([
            'rule' => [
                'profit'          => $profit,
                'source_tax'      => $sourceTax,
                'final_value_fee' => $finalValueFee,
                'paypal_rate'     => $paypalRate,
                'paypal_rate_usd' => $paypalRateUsd,
                'minimum_price'   => $minimumPrice,
            ],
        ]);

        $this->line('');
        $this->info("Created Reprice for item `{$this->argument('item_id')}`. Returned ID: {$repricer['id']}");
    }
}
