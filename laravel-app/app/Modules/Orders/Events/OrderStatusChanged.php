<?php

namespace App\Modules\Orders\Events;

class OrderStatusChanged
{
    public function __construct(
        public int $userId,
        public int $orderId,
        public string $from,
        public string $to,
    ) {
    }
}