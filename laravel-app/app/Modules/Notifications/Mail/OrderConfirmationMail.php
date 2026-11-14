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

    /**
     * تمرير كائن الطلب (Order) إلى الـ Mailable.
     */
    public function __construct(
        public Order $order
    ) {}

    /**
     * إعداد عنوان الرسالة (Subject).
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "تأكيد استلام طلبك رقم #{$this->order->id} - ShopApp",
        );
    }

    /**
     * تحديد قالب العرض والبيانات الممررة إليه.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.orders.confirmation',
            with: [
                'orderId' => $this->order->id,
                'customerName' => $this->order->user->name ?? 'عميلنا العزيز',
                'status' => $this->order->status,
                'total' => number_format((float) $this->order->total, 2),
            ],
        );
    }

    /**
     * المرفقات (لا يوجد مرفقات حالياً).
     */
    public function attachments(): array
    {
        return [];
    }
}