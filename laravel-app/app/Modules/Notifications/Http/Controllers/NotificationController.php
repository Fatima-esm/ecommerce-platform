<?php

namespace App\Modules\Notifications\Http\Controllers;

use App\Modules\Notifications\Models\UserNotification;
use Illuminate\Http\JsonResponse;

class NotificationController
{
    public function index(): JsonResponse
    {
        $items = UserNotification::query()
            ->where('user_id', auth('api')->id())
            ->orderByDesc('id')
            ->limit(50)
            ->get(['id', 'type', 'title', 'body', 'read_at', 'created_at']);

        return response()->json(['data' => $items]);
    }

    public function markRead(int $id): JsonResponse
    {
        $n = UserNotification::query()->where('user_id', auth('api')->id())->findOrFail($id);
        $n->update(['read_at' => now()]);

        return response()->json(['data' => ['id' => $n->id, 'read_at' => $n->read_at?->toIso8601String()]]);
    }
}
