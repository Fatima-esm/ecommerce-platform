<?php

namespace App\Modules\Notifications\Jobs;

use App\Modules\Orders\Models\Order; // المسار الصحيح
use App\Modules\Notifications\Mail\OrderConfirmationMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendOrderConfirmationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [10, 30, 60];

    public function __construct(public int $orderId)
    {
        $this->onConnection('rabbitmq');
        $this->onQueue('emails');
    }


    public function handle(): void
    {
        $order = Order::with('user')->findOrFail($this->orderId);

        Mail::to($order->user->email)
            ->send(new OrderConfirmationMail($order));
    }


    public function failed(Throwable $exception): void
    {
        Log::error('Order confirmation email failed', [
            'order_id' => $this->orderId,
            'error' => $exception->getMessage(),
        ]);
    }
}