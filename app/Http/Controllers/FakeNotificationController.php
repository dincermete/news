<?php

namespace App\Http\Controllers;

use App\Services\FakeOrderNotificationService;
use Illuminate\Http\JsonResponse;

class FakeNotificationController extends Controller
{
    public function __invoke(FakeOrderNotificationService $notifications): JsonResponse
    {
        $payload = $notifications->next();

        if ($payload === null) {
            return response()->json(['message' => null], 404);
        }

        return response()->json($payload);
    }
}
