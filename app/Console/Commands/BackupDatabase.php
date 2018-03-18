<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\MySqlConnection;
use Illuminate\Support\Carbon;
use Spatie\DbDumper\Databases\MySql as Dumper;

class BackupDatabase extends Command
{
    protected $signature = 'db:backup';

    protected $description = 'Backup Database';

    protected $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        parent::__construct();

        $this->filesystem = $filesystem;
    }

    public function handle()
    {
        #0. Preparing
        $this->prepare();

        #1. Backup Current Database Connections
        $this->backup();
        $this->info('√ Back Up Database.');

        #2. Clean Up
        $this->cleanUp();
        $this->info('√ Clean Up Old Backups.');
    }

    protected function backup()
    {
        $database = $this->config('database');
        $username = $this->config('username');
        $password = $this->config('password');
        $path     = $this->getPath();

        Dumper::create()
              ->setDbName($database)
              ->setUserName($username)
              ->setPassword($password)
              ->dumpToFile($path);
    }

    protected function cleanUp()
    {
        $files = $this->filesystem->allFiles('backups');

        collect($files)->each(function ($file) {
            #1. Keep Hourly Backup of Last 24 Hours
            $timestamp = explode('.', str_replace('backups/', '', $file))[1];

            $time = Carbon::createFromTimestamp($timestamp);

            if ($this->isHourlyBackup($time) && $time->lessThan(Carbon::now()->subHours(24))) {
                $this->filesystem->delete($file);
            }

            #2. Keep Daily Backup of Last 30 Days
            if ($this->isDailyBackup($time) && $time->lessThan(Carbon::today()->subDays(30))) {
                $this->filesystem->delete($file);
            }

            #3. Keep Weekly Backup of Last Year

            #4. Keep Monthly Backup of Last 5 Years
        });
    }

    protected function getFilename(): string
    {
        $database = $this->config('database');
        $time     = Carbon::now()->second(0)->minute(0)->timestamp;

        return "{$database}.{$time}.sql";
    }

    protected function config($option = null)
    {
        /** @var MySqlConnection $connection */
        $connection = app('db.connection');

        return $connection->getConfig($option);
    }

    protected function getPath(): string
    {
        return storage_path('app/backups/' . $this->getFilename());
    }

    protected function prepare()
    {
        // Create directory if existed
        if ( ! $this->filesystem->exists('backups')) {
            $this->filesystem->makeDirectory('backups');
        }
    }

    protected function isHourlyBackup(\Carbon\Carbon $time)
    {
        return $time->minute === 0 && $time->second === 0 && $time->hour !== 0;
    }

    protected function isDailyBackup(\Carbon\Carbon $time)
    {
        return $time->minute === 0 && $time->second === 0 && $time->hour === 0 && $time->day !== 0;
    }
}
