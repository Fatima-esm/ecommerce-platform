<?php

namespace App\Modules\Notifications\Listeners;

use App\Modules\Notifications\Models\UserNotification;
use App\Modules\Orders\Events\OrderCreated;
use App\Modules\Orders\Events\OrderStatusChanged;
use Illuminate\Support\Facades\Log;
use Throwable;

class OrderNotificationListener
{
    public function onCreated(OrderCreated $event): void
    {
        $this->notify(
            $event->userId,
            'order.created',
            'Order received',
            sprintf(
                'Your order #%d was created. Total: %s',
                $event->orderId,
                number_format($event->totalAmount, 2, '.', '')
            ),
        );
    }

    public function onStatusChanged(OrderStatusChanged $event): void
    {
        $this->notify(
            $event->userId,
            'order.status_changed',
            'Order status updated',
            sprintf(
                'Your order #%d changed from %s to %s.',
                $event->orderId,
                $event->from,
                $event->to
            ),
        );
    }

    private function notify(int $userId, string $type, string $title, string $body): void
    {
        try {
            UserNotification::create([
                'user_id' => $userId,
                'type' => $type,
                'title' => $title,
                'body' => $body,
            ]);
            Log::info('notification sent', ['user_id' => $userId, 'type' => $type]);
        } catch (Throwable $e) {
            Log::error('notification failed', [
                'user_id' => $userId,
                'type' => $type,
                'error' => $e->getMessage(),
            ]);
        }
    }
}