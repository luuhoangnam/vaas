<?php

namespace App\Console;

use App\Console\Commands\ApiUsage;
use App\Console\Commands\BulkImportTrackersByAccount;
use App\Console\Commands\ForceRefreshRanking;
use App\Console\Commands\ItemResearch;
use App\Console\Commands\LowPerformanceListing;
use App\Console\Commands\MakeRepricer;
use App\Console\Commands\PeriodicRefreshRank;
use App\Console\Commands\RunRepricerPeriodically;
use App\Console\Commands\ScanAmazonBestSellers;
use App\Console\Commands\SpyCompetitor;
use App\Console\Commands\SubscribePlatformNotification;
use App\Console\Commands\SyncAlleBayAccounts;
use App\Console\Commands\SynceBayAccount;
use App\Console\Commands\TrackRankingForAccount;
use App\Console\Commands\TrackRankingForItem;
use App\Console\Commands\UpdateeBayAPIQuota;
use App\Console\Commands\ViewCrawlerPerformance;
use App\Console\Commands\ViewRankingOfItem;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        SynceBayAccount::class,
        SyncAlleBayAccounts::class,
        LowPerformanceListing::class,
        SubscribePlatformNotification::class,
        ItemResearch::class,
        TrackRankingForItem::class,
        TrackRankingForAccount::class,
        PeriodicRefreshRank::class,
        ViewRankingOfItem::class,
        ForceRefreshRanking::class,
        BulkImportTrackersByAccount::class,
        ApiUsage::class,
        UpdateeBayAPIQuota::class,
        MakeRepricer::class,
        RunRepricerPeriodically::class,
        ScanAmazonBestSellers::class,
        ViewCrawlerPerformance::class,
        SpyCompetitor::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('api:quota')->everyFiveMinutes();
        $schedule->command('horizon:snapshot')->everyFiveMinutes();
        $schedule->command("ebay:sync:all --only_orders --since '3 hours ago'")->hourly(); // Fix
        $schedule->command('ranking:refresh')->weekly();
        $schedule->command('repricer:periodic')->everyFifteenMinutes();
        $schedule->command('db:backup')->hourly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
