<?php

namespace App\Modules\Orders\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusChanged implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $connection = 'rabbitmq';
    public string $queue = 'broadcasts';

    public function __construct(
        public int $userId,
        public int $orderId,
        public string $from,
        public string $to,
    ) {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('orders.' . $this->orderId),
            new PrivateChannel('admin.orders'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'order.status.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'order_id' => $this->orderId,
            'user_id' => $this->userId,
            'from' => $this->from,
            'to' => $this->to,
        ];
    }
}