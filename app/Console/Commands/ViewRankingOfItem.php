<?php

namespace App\Console\Commands;

use App\Item;
use App\Ranking\Tracker;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ViewRankingOfItem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ranking:show {item_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public function handle()
    {
        $item = Item::find($this->argument('item_id'));

        $trackers = $item->trackers()->get();

        if ($trackers->isEmpty()) {
            $this->info('No keyword tracked for this item.');
            $this->info('Add new keyword using: ranking:track {item_id} [--keyword=tracked_keyword]');

            return 0;
        }

        $this->info("Item: ({$item['item_id']}) {$item['title']}");

        $headers = ['Keyword', 'Rank (Change)'];

        $rows = collect();

        $trackers->each(function (Tracker $tracker) use ($rows) {
            $current = $tracker['current'];

            $change = $current['change'] ? ($current['change'] < 0 ?: "+{$current['change']}") : 'N/A';

            $rankAndChange = $current['rank'] ? "{$current['rank']} ({$change})" : 'N/A';

            $rows->push([$tracker['keyword'], $rankAndChange]);
        });

        $this->table($headers, $rows);
    }
}
