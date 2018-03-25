<?php

namespace App\Console\Commands;

use App\Account;
use App\Exceptions\TradingApiException;
use DTS\eBaySDK\Trading\Enums\AckCodeType;
use DTS\eBaySDK\Trading\Enums\DetailLevelCodeType;
use DTS\eBaySDK\Trading\Types\GetApiAccessRulesRequestType;
use Illuminate\Console\Command;

class UpdateeBayAPIQuota extends Command
{
    protected $signature = 'api:quota';

    protected $description = 'Update API Quota in Cache';

    public function handle()
    {
        $cacheTime = 60;

        list($usage, $softLimit, $hardLimit) = $this->usage();

        cache()->put('X-API-LIMIT-USAGE', $usage, $cacheTime);
        cache()->put('X-API-LIMIT-QUOTA', $hardLimit, $cacheTime);
    }

    protected function usage()
    {
        $request = new GetApiAccessRulesRequestType;

        $request->DetailLevel = [DetailLevelCodeType::C_RETURN_ALL];

        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $response = Account::random()->trading()->getApiAccessRules($request, false);

        if ($response->Ack === AckCodeType::C_FAILURE) {
            throw new TradingApiException($request, $response);
        }

        foreach ($response->ApiAccessRule as $rule) {
            if ($rule->CallName === 'ApplicationAggregate') {
                return [$rule->DailyUsage, $rule->DailySoftLimit, $rule->DailyHardLimit];
            }
        }

        return null;
    }
}
