<?php

namespace App\Http\Controllers;

use App\Events\LiveSessionUpdated;
use App\Models\LiveSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LiveHeartbeatController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'session_token' => ['required', 'string', 'max:128'],
            'current_url' => ['required', 'string', 'max:2048'],
        ]);

        $session = LiveSession::upsertHeartbeat($data['session_token'], [
            'current_url' => $data['current_url'],
            'user_id' => $request->user()?->id,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        LiveSessionUpdated::dispatch($session);

        return response()->json([
            'ok' => true,
            'session_id' => $session->id,
            'last_seen_at' => $session->last_seen_at?->toIso8601String(),
        ]);
    }
}
