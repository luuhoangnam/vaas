<?php

namespace App\Jobs;

use App\Account;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;

class UpdateAPIUsage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        $cacheTime = 60;

        list($usage, $softLimit, $quota) = Account::random()->trading()->usage();

        cache()->put('X-API-LIMIT-USAGE', $usage, $cacheTime);
        cache()->put('X-API-LIMIT-QUOTA', $quota, $cacheTime);

        $this->updateOnFirebase($usage, $quota);
    }

    protected function updateOnFirebase($usage, $quota)
    {
        $serviceAccount = ServiceAccount::fromJsonFile(resource_path('credentials/vaas-quick-863f4a7a64e2.json'));

        $firebase = (new Factory)->withServiceAccount($serviceAccount)
                                 ->withDatabaseUri('https://vaas-quick.firebaseio.com/')
                                 ->create();

        $firebase->getDatabase()->getReference('api_limit')->update(compact('usage', 'quota'));
    }
}
