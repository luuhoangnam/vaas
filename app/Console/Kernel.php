<?php

namespace App\Console;

use App\Console\Commands\ApiUsage;
use App\Console\Commands\BulkImportTrackersByAccount;
use App\Console\Commands\ForceRefreshRanking;
use App\Console\Commands\ItemResearch;
use App\Console\Commands\LowPerformanceListing;
use App\Console\Commands\PeriodicRefreshRank;
use App\Console\Commands\SubscribePlatformNotification;
use App\Console\Commands\SynceBayAccount;
use App\Console\Commands\TrackRankingForAccount;
use App\Console\Commands\TrackRankingForItem;
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
        $schedule->command('ranking:refresh')->daily();
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
