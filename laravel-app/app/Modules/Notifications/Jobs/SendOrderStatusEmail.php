<?php

namespace App\Modules\Notifications\Jobs;

use App\Modules\Orders\Models\Order;
use App\Modules\Notifications\Mail\OrderStatusMail;
use App\Modules\Notifications\Services\EmailDeliveryGuard;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendOrderStatusEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [10, 30, 60];

    public function __construct(
        public int $orderId,
        public string $newStatus
    ) {
        $this->onConnection('rabbitmq');
        $this->onQueue('emails');
    }

    public function handle(EmailDeliveryGuard $guard): void
    {
        $eventKey = 'status:' . $this->newStatus;

        if (!$guard->claim($this->orderId, $eventKey)) {
            Log::info('Status email skipped', [
                'order_id' => $this->orderId,
                'status' => $this->newStatus,
            ]);

            return;
        }

        try {
            $order = Order::with('user')->findOrFail($this->orderId);

            // If the order status has changed since the job was dispatched, we skip sending the email.
            if ($order->status !== $this->newStatus) {

                $guard->markFailed(
                    $this->orderId,
                    $eventKey,
                    'Order status changed before email processing.'
                );

                Log::info('Status email skipped: status changed', [
                    'order_id' => $this->orderId,
                    'expected_status' => $this->newStatus,
                    'current_status' => $order->status,
                ]);

                return;
            }

            Mail::to($order->user->email)->send(
                new OrderStatusMail(order: $order)
            );

            $guard->markSent($this->orderId, $eventKey);

            Log::info('Order status email sent successfully', [
                'order_id' => $this->orderId,
                'status' => $this->newStatus,
            ]);

        } catch (Throwable $exception) {

            $guard->markFailed(
                $this->orderId,
                $eventKey,
                $exception->getMessage()
            );

            throw $exception;
        }
    }

    public function failed(Throwable $exception): void
    {
        app(EmailDeliveryGuard::class)->markFailed(
            $this->orderId,
            'status:' . $this->newStatus,
            $exception->getMessage()
        );

        Log::error('Order status email failed', [
            'order_id' => $this->orderId,
            'status' => $this->newStatus,
            'error' => $exception->getMessage(),
        ]);
    }
}
