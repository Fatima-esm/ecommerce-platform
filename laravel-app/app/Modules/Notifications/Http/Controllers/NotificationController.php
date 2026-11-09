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
            ->where('id', $id)
            ->first();

        if (!$n) {
            return response()->json([
                'error' => [
                    'status' => 404,
                    'code' => 'NOT_FOUND',
                    'message' => 'Notification not found',
                    'details' => null,
                ],
            ], 404);
        }

        $n->update(['read_at' => now()]);

        return response()->json([
            'data' => [
                'id' => $n->id,
                'read_at' => optional($n->read_at)->toIso8601String(),
            ],
        ]);
    }
}