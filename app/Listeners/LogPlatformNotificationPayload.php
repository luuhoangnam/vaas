<?php

namespace App\Listeners;

use App\Events\PlatformNotificationReceived;
use App\Support\PlatformNotificaitonRequestParser;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class LogPlatformNotificationPayload
{
    protected $fs;

    public function __construct(Filesystem $fs)
    {
        $this->fs = $fs;
    }

    public function handle(PlatformNotificationReceived $event)
    {
        $parser = new PlatformNotificaitonRequestParser($event->request);

        $payload = $parser->payload();

        $timestamp = time();

        $random = rand(1, 999999);

        $fileName = "{$payload->NotificationEventName}.{$timestamp}.{$random}.xml";
        $filePath = storage_path("app/notifications/{$fileName}");

        $xmlContent = $event->request->getContent();

        $this->fs->put($filePath, $xmlContent);
    }
}
