<?php

namespace App\Modules\Notifications\Models;

use Illuminate\Database\Eloquent\Model;

class EmailDelivery extends Model
{
    protected $fillable = [
        'order_id',
        'event_key',
        'status',
        'sent_at',
        'last_error',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];
}
