<?php

namespace App\Events\PlatformNotifications;

use App\Item;
use DTS\eBaySDK\Trading\Types\GetItemTransactionsResponseType;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FixedPriceTransaction implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var GetItemTransactionsResponseType
     */
    public $payload;

    /**
     * Create a new event instance.
     *
     * @param $payload
     */
    public function __construct($payload)
    {
        $this->payload = $payload;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel("ebay.orders");
    }

    public function broadcastAs()
    {
        return 'ebay.new_order';
    }

    public function broadcastWith()
    {
        return Item::extractItemAttributes($this->payload->Item);
    }
}
