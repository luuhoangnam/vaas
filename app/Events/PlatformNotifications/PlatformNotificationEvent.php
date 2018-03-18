<?php

namespace App\Events\PlatformNotifications;

use DTS\eBaySDK\Trading\Types\AbstractResponseType;
use DTS\eBaySDK\Types\BaseType;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class PlatformNotificationEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var array
     */
    protected $payload;

    /**
     * Create a new event instance.
     *
     * @param $payload
     */
    public function __construct($payload)
    {
        if ($payload instanceof BaseType) {
            $payload = $payload->toArray();
        }

        $this->payload = $payload;
    }

    public function getPayload(): AbstractResponseType
    {
        $payloadClass = $this->getPayloadClass();

        if (is_array($this->payload) && class_exists($payloadClass)) {
            return new $payloadClass($this->payload);
        }

        throw new \RuntimeException('Invalid Event Payload Type');
    }

    abstract protected function getPayloadClass(): string;
}