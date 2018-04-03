<?php

namespace App\Jobs;

use App\Account;
use App\eBay\App;
use App\eBay\TradingAPI;
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

    /**
     * @throws \Exception
     */
    public function handle()
    {
        $apps = cache()->remember('ebay.apps', 1 / 6, function () {
            return App::query()->get(['app_id', 'dev_id', 'cert_id', 'token'])->toArray();
        });

        $apps = array_merge($apps, config('ebay.apps'));

        collect($apps)->each(function ($credentials) {
            list($usage, $softLimit, $quota) = TradingAPI::build($credentials)->usage();

            cache()->put("apps.{$credentials['app_id']}.trading.usage", $usage, 60);
            cache()->put("apps.{$credentials['app_id']}.trading.quota", $quota, 60);

            $this->updateOnFirebase($credentials['app_id'], $usage, $quota);
        });
    }

    protected function updateOnFirebase($appId, $usage, $quota)
    {
        $serviceAccount = ServiceAccount::fromJsonFile(resource_path('credentials/vaas-quick-863f4a7a64e2.json'));

        $firebase = (new Factory)->withServiceAccount($serviceAccount)
                                 ->withDatabaseUri('https://vaas-quick.firebaseio.com/')
                                 ->create();

        $firebase->getDatabase()->getReference("apps/{$appId}")->update(compact('usage', 'quota'));
    }
}
