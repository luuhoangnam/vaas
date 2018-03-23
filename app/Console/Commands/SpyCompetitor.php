<?php

namespace App\Console\Commands;

use App\Account;
use App\Spy\Competitor;
use Illuminate\Console\Command;

class SpyCompetitor extends Command
{
    protected $signature = 'competitor:spy {username} {--W|watch}';

    protected $description = 'Spy Competitor And Their Sale Performance';

    public function handle()
    {
        $username = $this->argument('username');

        if (Competitor::exists($username)) {
            $this->info('Competitor Existed.');

            return;
        }

        Competitor::spy($username, $this->option('watch'));

        $this->info('Done!');
    }

    public function trading()
    {
        return Account::random()->trading();
    }
}
