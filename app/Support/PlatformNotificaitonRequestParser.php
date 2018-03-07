<?php

namespace App\Support;

use DTS\eBaySDK\Parser\XmlParser;
use DTS\eBaySDK\Trading\Types\AbstractResponseType;
use Illuminate\Http\Request;
use Sabre\Xml\Service as XmlService;

class PlatformNotificaitonRequestParser
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function payload(): AbstractResponseType
    {
        $xmlService = new XmlService;

        $result = $xmlService->parse($this->request->getContent());

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