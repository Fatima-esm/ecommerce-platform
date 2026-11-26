<?php

namespace App\Modules\Notifications\Jobs;

use App\Modules\Orders\Models\Order;
use App\Modules\Notifications\Mail\OrderConfirmationMail;
use App\Modules\Notifications\Services\EmailDeliveryGuard;
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

    public function handle(EmailDeliveryGuard $guard): void
    {
        $eventKey = 'confirmation';

        // If another worker has already claimed it, we skip sending the email.
        if (!$guard->claim($this->orderId, $eventKey)) {
            Log::info('Confirmation email skipped', [
                'order_id' => $this->orderId,
            ]);

            return;
        }

        try {
            $order = Order::with('user')->findOrFail($this->orderId);

            Mail::to($order->user->email)
                ->send(new OrderConfirmationMail($order));

            // record the successful delivery
            $guard->markSent($this->orderId, $eventKey);

            Log::info('Confirmation email sent successfully', [
                'order_id' => $this->orderId,
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
            'confirmation',
            $exception->getMessage()
        );

        Log::error('Order confirmation email failed', [
            'order_id' => $this->orderId,
            'error' => $exception->getMessage(),
        ]);
    }
    }