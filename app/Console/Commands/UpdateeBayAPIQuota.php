<?php

namespace App\Console\Commands;

use App\Account;
use App\Exceptions\TradingApiException;
use DTS\eBaySDK\Trading\Enums\AckCodeType;
use DTS\eBaySDK\Trading\Enums\DetailLevelCodeType;
use DTS\eBaySDK\Trading\Types\GetApiAccessRulesRequestType;
use Illuminate\Console\Command;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;

class UpdateeBayAPIQuota extends Command
{
    protected $signature = 'api:quota';

    protected $description = 'Update API Quota in Cache and Firebase';

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

        $firebase = (new Factory)
            ->withServiceAccount($serviceAccount)
            // The following line is optional if the project id in your credentials file
            // is identical to the subdomain of your Firebase project. If you need it,
            // make sure to replace the URL with the URL of your project.
            ->withDatabaseUri('https://vaas-quick.firebaseio.com/')
            ->create();

        $firebase->getDatabase()->getReference('api_limit')->update(compact('usage', 'quota'));
    }
}
