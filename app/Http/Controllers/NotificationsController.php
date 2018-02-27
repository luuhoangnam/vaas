<?php

namespace App\Http\Controllers;

use DTS\eBaySDK\Parser\XmlParser;
use DTS\eBaySDK\Trading\Types\AbstractResponseType;
use Illuminate\Http\Request;
use Sabre\Xml\Service as XmlService;

class NotificationsController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $this->parseXml($request);

        $eventName = $payload->NotificationEventName;

        $eventClass = "App\\Events\\PlatformNotifications\\{$eventName}";

        // Only Raise Event when It Has an Event Class to Handle
        if (class_exists($eventClass)) {
            event(new $eventClass($payload));
        }

        return 'ok';
    }

    protected function parseXml(Request $request): AbstractResponseType
    {
        $xmlService = new XmlService;

        $result = $xmlService->parse($request->getContent());

        $xmlService->namespaceMap['urn:ebay:apis:eBLBaseComponents'] = '';

        $element = $result[1]['value'][0];

        $xml = $xmlService->write(
            $element['name'],
            $element['value']
        );

        $responseClassName = str_replace('{urn:ebay:apis:eBLBaseComponents}', '', $element['name']);

        $responseClass = "DTS\\eBaySDK\\Trading\\Types\\{$responseClassName}Type";

        $xmlParser = new XmlParser($responseClass);

        return $xmlParser->parse($xml);
    }
}
