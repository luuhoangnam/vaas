<?php

namespace App\Console\Commands;

use App\Account;
use Illuminate\Console\Command;

class TrackRankingForAccount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ranking:track:account {username} {--K|keyword=}';

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

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $account = Account::find($this->argument('username'));

        if ( ! $this->option('keyword')) {
            $this->error('You have to supply --keyword (with value)');

            return 1;
        }

        if ($this->option('keyword')) {
            $account->track($this->option('keyword'));
        }

        $this->info('Done!');

        return 0;
    }
}
