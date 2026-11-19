<?php

use Illuminate\Support\Facades\Broadcast;
use App\Modules\Orders\Models\Order;


    Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
        return (int) $user->id === (int) $id;
    });

    Broadcast::channel('orders.{orderId}', function ($user, $orderId) {
        $order = Order::find($orderId);

        if (!$order) {
            return false;
        }

        return (int) $user->id === (int) $order->user_id || $user->isAdmin();
    });

    Broadcast::channel('admin.orders', function ($user) {
        return $user->isAdmin();
    });