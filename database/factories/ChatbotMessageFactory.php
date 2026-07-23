<?php

namespace Database\Factories;

use App\Enums\ChatbotMessageRole;
use App\Models\ChatbotConversation;
use App\Models\ChatbotMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChatbotMessage>
 */
class ChatbotMessageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'chatbot_conversation_id' => ChatbotConversation::factory(),
            'session_token' => fake()->uuid(),
            'role' => ChatbotMessageRole::User,
            'content' => fake()->sentence(),
        ];
    }

    public function forConversation(ChatbotConversation $conversation): static
    {
        return $this->state(fn (array $attributes): array => [
            'chatbot_conversation_id' => $conversation->id,
            'session_token' => $conversation->session_token,
        ]);
    }
}
