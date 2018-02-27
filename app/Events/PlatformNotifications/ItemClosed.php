<?php

namespace App\Events\PlatformNotifications;

use App\Item;
use DTS\eBaySDK\Trading\Types\GetItemResponseType;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ItemClosed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var GetItemResponseType
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
        return new PrivateChannel("ebay.item.{$this->payload->Item->ItemID}");
    }

    public function broadcastAs()
    {
        return 'ebay.item.closed';
    }

    public function broadcastWith()
    {
        return Item::extractItemAttributes($this->payload->Item);
    }
}
