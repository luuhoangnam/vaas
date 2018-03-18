<?php

namespace App\Console\Commands;

use App\Jobs\ScanAmazonBestSellerPage;
use Illuminate\Console\Command;

class ScanAmazonBestSellers extends Command
{
    protected $signature = 'amazon:best-seller';

    public function handle()
    {
        $lists = config('amazon.best_sellers', []);

        collect($lists)->shuffle()->each(function ($link) {

            ScanAmazonBestSellerPage::dispatch($link);

        });
    }
}
