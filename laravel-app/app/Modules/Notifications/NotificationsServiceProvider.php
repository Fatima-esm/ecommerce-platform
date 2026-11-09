<?php

namespace App\Modules\Notifications\Http\Controllers;

use App\Modules\Notifications\Models\UserNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController
{
    public function index(Request $request): JsonResponse
    {
        $items = UserNotification::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('id')
            ->limit(50)
            ->get(['id', 'type', 'title', 'body', 'read_at', 'created_at']);

        return response()->json(['data' => $items]);
    }

    public function markRead(Request $request, int $id): JsonResponse
    {
        $n = UserNotification::query()
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        $n->update(['read_at' => now()]);

        return response()->json([
            'data' => [
                'id' => $n->id,
                'read_at' => $n->read_at?->toIso8601String(),
            ],
        ]);
    }
}