<?php

namespace App\Http\Controllers;

use App\Services\AiChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatbotMessageController extends Controller
{
    public function __invoke(Request $request, AiChatbotService $chatbot): JsonResponse
    {
        $data = $request->validate([
            'session_token' => ['required', 'string', 'max:128'],
            'message' => ['required', 'string', 'max:4000'],
        ]);

        $result = $chatbot->respond($data['session_token'], $data['message']);

        return response()->json($result);
    }
}
