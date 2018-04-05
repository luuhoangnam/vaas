<?php

namespace App\Http\Controllers\eBay;

use App\Events\PlatformNotificationReceived;
use App\Http\Controllers\Controller;
use App\Support\PlatformNotificaitonRequestParser;
use DTS\eBaySDK\Trading\Types\AbstractResponseType;
use Illuminate\Http\Request;

class NotificationsController extends Controller
{
    public function handle(Request $request)
    {
        // Fire Default Event
        event(new PlatformNotificationReceived($request));

        $payload = $this->parsePayload($request);

        $eventName = $payload->NotificationEventName;

        $eventClass = "App\\Events\\PlatformNotifications\\{$eventName}";

        // Only Fire Event when It Has an Event Class to Handle
        if (class_exists($eventClass)) {
            event(new $eventClass($payload));
        }

        return 'ok';
    }

    protected function parsePayload(Request $request): AbstractResponseType
    {
        $parser = new PlatformNotificaitonRequestParser($request);

        return $parser->payload();
    }
}
