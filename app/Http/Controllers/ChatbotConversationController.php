<?php

namespace App\Http\Controllers;

use App\Enums\ChatbotMessageRole;
use App\Models\ChatbotConversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatbotConversationController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'session_token' => ['required', 'string', 'max:128'],
        ]);

        $conversation = ChatbotConversation::query()
            ->where('session_token', $data['session_token'])
            ->first();

        if ($conversation === null) {
            return response()->json(['messages' => []]);
        }

        $messages = $conversation->messages()
            ->where('role', '!=', ChatbotMessageRole::System)
            ->orderBy('id')
            ->limit(50)
            ->get(['role', 'content', 'created_at'])
            ->map(fn ($message): array => [
                'role' => $message->role instanceof ChatbotMessageRole ? $message->role->value : (string) $message->role,
                'content' => $message->content,
                'created_at' => $message->created_at?->toIso8601String(),
                'created_at_display' => $message->created_at?->diffForHumans(),
            ])
            ->all();

        return response()->json(['messages' => $messages]);
    }
}
