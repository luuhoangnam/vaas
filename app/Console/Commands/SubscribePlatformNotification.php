<?php

namespace App\Console\Commands;

use App\Account;
use App\Exceptions\TradingApiException;
use DTS\eBaySDK\Trading\Enums\EnableCodeType;
use DTS\eBaySDK\Trading\Enums\NotificationEventTypeCodeType;
use DTS\eBaySDK\Trading\Types\ApplicationDeliveryPreferencesType;
use DTS\eBaySDK\Trading\Types\NotificationEnableArrayType;
use DTS\eBaySDK\Trading\Types\NotificationEnableType;
use Illuminate\Console\Command;

class SubscribePlatformNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ebay:notification:subscribe {username}';

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
        $account = $this->getAccount();

        try {

            $account->subscribePlatformNotification();

        } catch (TradingApiException $exception) {
            $this->error('Can not subscribe to platform notification event');

            return 1;
        }

        $this->info('Done!');

        return 0;
    }

    protected function getAccount(): Account
    {
        return Account::query()->where('username', $this->argument('username'))->firstOrFail();
    }
}
