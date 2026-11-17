<?php

namespace App\Modules\Orders\Events; // (عدّل المسار حسب هيكل موديولات مشروعك)

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderCreated implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $connection = 'rabbitmq';
    public string $queue = 'broadcasts';

    public function __construct(
        public int $orderId,
        public int $userId,
        public string $status,
        public float $totalAmount,  
    ) {
        
    }

    /**
     * certain channels that the event should broadcast on. In this case,
     * we are broadcasting to a private channel for the specific order and a general admin channel.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('orders.' . $this->orderId),
            new PrivateChannel('admin.orders'),
        ];
    }

    /**
     * The name of the event when broadcasting. This is useful for front-end listeners to identify the event type.
     */
    public function broadcastAs(): string
    {
        return 'order.created';
    }

    /**
     * The data that should be broadcast with the event.
     * This is the payload that will be sent to the front-end listeners.
     */
    public function broadcastWith(): array
    {
        return [
            'order_id' => $this->orderId,
            'user_id' => $this->userId,
            'status' => $this->status,
            'total_amount' => $this->totalAmount,
        ];
    }
}