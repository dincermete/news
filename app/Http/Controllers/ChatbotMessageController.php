<?php

namespace App\Http\Controllers;

use App\Services\AiChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class ChatbotMessageController extends Controller
{
    public function __invoke(Request $request, AiChatbotService $chatbot): JsonResponse
    {
        $data = $request->validate([
            'session_token' => ['required', 'string', 'max:128'],
            'message' => ['required', 'string', 'max:4000'],
        ]);

        try {
            $result = $chatbot->respond($data['session_token'], $data['message']);
        } catch (Throwable $exception) {
            // AiChatbotService already retries with a graceful [ESCALATE] fallback internally;
            // this is a last-resort guard so the widget always receives valid JSON, never an
            // HTML error page it can't parse.
            Log::error('Chatbot mesaj işlenemedi.', [
                'session_token' => $data['session_token'],
                'exception' => $exception->getMessage(),
            ]);

            return response()->json([
                'reply' => 'Şu anda size yanıt veremiyorum. Lütfen birazdan tekrar deneyin.',
                'escalation' => null,
                'suggestions' => [],
            ], 200);
        }

        return response()->json($result);
    }
}
