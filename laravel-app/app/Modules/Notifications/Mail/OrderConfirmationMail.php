<?php

namespace App\Modules\Notifications\Mail;

use App\Modules\Orders\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Order Confirmation #{$this->order->id} - ShopApp",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.orders.confirmation',
            with: [
                'orderId' => $this->order->id,
                'customerName' => $this->order->user->name ?? 'Dear Customer',
                'status' => $this->order->status,
                'total' => number_format((float) ($this->order->total_amount ?? $this->order->getAttribute('total_amount')), 2),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}