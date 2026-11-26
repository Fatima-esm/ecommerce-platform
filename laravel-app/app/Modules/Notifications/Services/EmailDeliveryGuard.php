<?php

namespace App\Modules\Notifications\Services;

use Illuminate\Support\Facades\DB;

class EmailDeliveryGuard
{
    /**
     * try to claim the email delivery for the given order and event key.
     *
     * @return bool
     */
    public function claim(int $orderId, string $eventKey): bool
    {
        // create a record if it doesn't exist yet. The unique constraint prevents creating a duplicate record.
        DB::table('email_deliveries')->insertOrIgnore([
            'order_id' => $orderId,
            'event_key' => $eventKey,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::transaction(function () use ($orderId, $eventKey) {

            $delivery = DB::table('email_deliveries')
                ->where('order_id', $orderId)
                ->where('event_key', $eventKey)
                ->lockForUpdate()
                ->first();

            if (!$delivery) {
                return false;
            }

            // record already sent, no need to send again
            if ($delivery->status === 'sent') {
                return false;
            }

            // another worker has started sending this message
            if ($delivery->status === 'sending') {
                return false;
            }

            // can retry the failed message
            DB::table('email_deliveries')
                ->where('id', $delivery->id)
                ->update([
                    'status' => 'sending',
                    'last_error' => null,
                    'updated_at' => now(),
                ]);

            return true;
        });
    }

    /**
     * mark the email delivery as sent.
     */
    public function markSent(int $orderId, string $eventKey): void
    {
        DB::table('email_deliveries')
            ->where('order_id', $orderId)
            ->where('event_key', $eventKey)
            ->where('status', 'sending')
            ->update([
                'status' => 'sent',
                'sent_at' => now(),
                'last_error' => null,
                'updated_at' => now(),
            ]);
    }

    /**
     * mark the email delivery as failed.
     */
    public function markFailed(
        int $orderId,
        string $eventKey,
        string $error
    ): void {
        DB::table('email_deliveries')
            ->where('order_id', $orderId)
            ->where('event_key', $eventKey)
            ->where('status', 'sending')
            ->update([
                'status' => 'failed',
                'last_error' => $error,
                'updated_at' => now(),
            ]);
    }
}
