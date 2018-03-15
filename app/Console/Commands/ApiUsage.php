<?php

namespace App\Console\Commands;

use App\Account;
use App\Exceptions\TradingApiException;
use DTS\eBaySDK\Trading\Enums\AckCodeType;
use DTS\eBaySDK\Trading\Enums\DetailLevelCodeType;
use Illuminate\Console\Command;

class ApiUsage extends Command
{
    protected $signature = 'api:usage';

    protected $description = 'Get eBay API Usage';

    public function handle()
    {
        $this->printApiCallUsage(Account::random());
    }

    protected function printApiCallUsage(Account $account): void
    {
        $request = $account->getApiAccessRulesRequest();

        $request->DetailLevel = [DetailLevelCodeType::C_RETURN_ALL];

        $response = $account->trading()->getApiAccessRules($request);

        if ($response->Ack === AckCodeType::C_FAILURE) {
            throw new TradingApiException($request, $response);
        }

        $this->info('API Access Rules');
        $this->line('');

        $headers = ['Call Name', 'Hourly (Usage/Soft/Hard)', 'Daily (Usage/Soft/Hard)'];

        $rows = collect();
        foreach ($response->ApiAccessRule as $rule) {
            $hourly = "{$rule->HourlyUsage}/{$rule->HourlySoftLimit}/{$rule->HourlyHardLimit}";
            $daily  = "{$rule->DailyUsage}/{$rule->DailySoftLimit}/{$rule->DailyHardLimit}";

            $rows->push([$rule->CallName, $hourly, $daily]);
        }

        $this->table($headers, $rows);

        $this->line('');
    }
}
