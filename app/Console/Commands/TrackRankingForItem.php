<?php

namespace App\Console\Commands;

use App\Item;
use Illuminate\Console\Command;

class TrackRankingForItem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ranking:track {item_id} {--K|keyword=} {--title}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $item = Item::find($this->argument('item_id'));

        if ( ! $this->option('keyword') && ! $this->option('title')) {
            $this->error('You have to supply either --keyword (with value) or --title');

            return 1;
        }

        if ($this->option('keyword')) {
            $item->track($this->option('keyword'));
        }

        if ($this->option('title')) {
            $item->track($item['title']);
        }

        $this->info('Done!');

        return 0;
    }
}
