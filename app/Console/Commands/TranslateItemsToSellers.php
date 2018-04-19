<?php

namespace App\Console\Commands;

use App\eBay\TradingAPI;
use DTS\eBaySDK\Trading\Types\GetItemRequestType;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Filesystem\Filesystem;

class TranslateItemsToSellers extends Command
{
    protected $signature = 'trans-items {--I|input=items.txt} {--O|output=sellers.txt}';
    protected $description = 'Translate a list of items into another list of equivalent sellers';

    protected $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        parent::__construct();

        $this->filesystem = $filesystem;
    }

    public function handle()
    {
        $input = $this->option('input') ?? 'items.txt';
        $output = $this->option('output') ?? 'sellers.txt';

        try {
            $itemsTxt = $this->filesystem->get($input);
        } catch (FileNotFoundException $exception) {
            return $this->error("Input file not found: {$input}");
        }

        $items = collect(explode("\n", $itemsTxt));

        $this->output->progressStart($items->count());

        $sellers = $items->map(function ($id) {

            try {
                $request = new GetItemRequestType;

                $request->ItemID = (string)$id;

                $request->OutputSelector = [
                    'Item.Seller.UserID',
                ];

                $response = TradingAPI::random()->getItem($request);

                $this->output->progressAdvance();

                return $response->Item->Seller->UserID;
            } catch (Exception $exception) {
                return '<Invalid Seller>';
            }
        })->unique();

        $this->output->progressFinish();

        $this->filesystem->put($output, $sellers->implode("\n"));

        $this->info('Done!');
    }
}
