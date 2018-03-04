<?php

namespace App\Console\Commands;

use App\Account;
use App\Item;
use App\Ranking\Tracker;
use Illuminate\Console\Command;

class ForceRefreshRanking extends Command
{
    protected $signature = 'ranking:force-refresh {--T|tracker_id=} {--I|item_id=} {--A|account_id=} {--U|username=}';

    protected $description = 'Command description';

    public function handle()
    {
        if ($this->option('tracker_id')) {
            Tracker::find($this->option('tracker_id'))->refresh();
        }

        if ($this->option('item_id')) {
            Item::find($this->option('item_id'))->trackers()->get()->shuffle()->each(function (Tracker $tracker) {
                $tracker->refresh();
            });
        }

        if ($this->option('account_id')) {
            /** @var Account $account */
            $account = Account::query()
                              ->with('trackers')
                              ->where('id', $this->option('account_id'))
                              ->firstOrFail();

            $account->trackers()->get()->shuffle()->each(function (Tracker $tracker) {
                $tracker->refresh();
            });
        }

        if ($this->option('username')) {
            Account::find($this->option('username'))
                   ->trackers()
                   ->get()
                   ->shuffle()
                   ->each(function (Tracker $tracker) {
                       $tracker->refresh();
                   });
        }

        $this->info('Done!');

        return 0;
    }
}
