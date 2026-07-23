<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MarkNotificationReadController extends Controller
{
    public function __invoke(Request $request, UserNotification $userNotification): JsonResponse
    {
        abort_unless($userNotification->user_id === $request->user()->id, 403);

        $userNotification->markAsRead();

        $unreadCount = $request->user()
            ->notificationsInbox()
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'ok' => true,
            'unread_count' => $unreadCount,
        ]);
    }
}
