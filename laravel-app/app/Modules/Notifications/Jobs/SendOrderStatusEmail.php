<?php

namespace App\Modules\Notifications\Jobs;

use App\Modules\Orders\Models\Order;
use App\Modules\Notifications\Mail\OrderStatusMail;
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

    public function handle(): void
    {
        $order = Order::with('user')->findOrFail($this->orderId);

        if ($order->status !== $this->newStatus) {
            Log::info('Order status email skipped: status has changed', [
                'order_id' => $this->orderId,
                'expected_status' => $this->newStatus,
                'current_status' => $order->status,
            ]);

            return;
        }

        Mail::to($order->user->email)->send(
            new OrderStatusMail(
                order: $order
            )
        );
        
        Log::info('Order status email sent successfully', [
            'order_id' => $this->orderId,
            'status' => $this->newStatus,
        ]);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Order status email failed', [
            'order_id' => $this->orderId,
            'status' => $this->newStatus,
            'error' => $exception->getMessage(),
        ]);
    }
}
