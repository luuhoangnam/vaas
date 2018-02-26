<?php

namespace App\Console\Commands;

use App\Account;
use App\Exceptions\CanNotEndItemsException;
use App\Item;
use DTS\eBaySDK\Trading\Enums\EndReasonCodeType;
use DTS\eBaySDK\Trading\Types\EndItemRequestContainerType;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class LowPerformanceListing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ebay:performance:unsold {username} {--days=14}';

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
        $username = $this->argument('username');
        $days     = (int)$this->option('days');

        $account = $this->getAccount($username);

        $lowPerformanceItems = $account->unsoldItemsByListingAge($days)
                                       ->get(['item_id', 'title', 'price', 'start_time']);

        $this->info("There are {$lowPerformanceItems->count()} unsold listings has age > {$days} days.");

        $headers = ['Item ID', 'Title', 'Price', 'Since'];

        $this->table($headers, $lowPerformanceItems);

        if ($lowPerformanceItems->count() === 0) {
            $this->info('Done!');

            return 0;
        }

        $willEndListings = $this->choice(
            'Do you wanna end these listings?',
            ['No', 'Yes'],
            1
        );

        $willEndListings = $willEndListings === 'Yes' ? true : false;

        if ( ! $willEndListings) {
            $this->info('Bye!');

            return 0;
        }

        $this->warn('Ending Items...');

        // END LISTING BY REQUESTED
        $lowPerformanceItems->chunk(10)->each(function (Collection $itemSet) use ($account) {

            $request = $account->endItemsRequest();

            foreach ($itemSet as $item) {
                $container = new EndItemRequestContainerType;

                $container->ItemID       = $item['item_id'];
                $container->EndingReason = EndReasonCodeType::C_NOT_AVAILABLE;
                $container->MessageID    = $item['item_id'];

                $request->EndItemRequestContainer[] = $container;
            }

            $response = $account->trading()->endItems($request);

            if ($response->Ack !== 'Success') {
                if ($response->Errors[0]->ErrorCode === "400") {
                    $this->error('Error happens in several items (may be items already ended).');

                    return; // Move to next chunk of items
                } else {
                    throw new CanNotEndItemsException($request, $response);
                }
            }

            $items = array_combine($itemSet->pluck('item_id')->toArray(), $itemSet->toArray());

            foreach ($response->toArray()['EndItemResponseContainer'] as $result) {
                $title = $items[$result['CorrelationID']]['title'];
                $time  = app_carbon($result['EndTime']);

                $this->info("{$time->toDateTimeString()}: ({$result['CorrelationID']}) {$title}");

                // Manual Update in Database
                Item::find($result['CorrelationID'])->update(['status' => 'Completed']);
            }
        });

        $this->info('Done!');
    }

    protected function getAccount($username): Account
    {
        return Account::query()->where('username', $username)->firstOrFail();
    }
}
