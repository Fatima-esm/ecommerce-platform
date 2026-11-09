<?php

namespace App\Modules\Orders\Events;

class OrderCreated
{
    public function __construct(
        public int $userId,
        public int $orderId,
        public float $totalAmount,
    ) {
    }
}