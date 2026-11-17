<?php

namespace App\Modules\Notifications\Mail;

use App\Modules\Orders\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public string $oldStatus = ''
    ) {}

    public function envelope(): Envelope
    {
        $subject = match ($this->order->status) {
            'confirmed' => "Your order has been confirmed #{$this->order->id}",
            'cancelled' => "Your order has been cancelled #{$this->order->id}",
            default => "Update on your order #{$this->order->id}",
        };

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.orders.order-status',
            with: [
                'orderId' => $this->order->id,
                'customerName' => $this->order->user->name ?? 'Dear Customer',
                'status' => $this->order->status,
                'oldStatus' => $this->oldStatus,
                'total' => number_format((float) ($this->order->total_amount ?? $this->order->getAttribute('total_amount')), 2),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}