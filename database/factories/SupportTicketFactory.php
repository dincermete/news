<?php

namespace Database\Factories;

use App\Enums\SupportTicketSource;
use App\Enums\SupportTicketStatus;
use App\Models\ChatbotConversation;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupportTicket>
 */
class SupportTicketFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'subject' => fake()->sentence(4),
            'body' => fake()->paragraph(),
            'status' => SupportTicketStatus::Open,
            'source' => SupportTicketSource::Manual,
            'chatbot_conversation_id' => null,
        ];
    }

    public function fromChatbot(?ChatbotConversation $conversation = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'source' => SupportTicketSource::ChatbotEscalation,
            'chatbot_conversation_id' => $conversation?->id ?? ChatbotConversation::factory(),
        ]);
    }
}
