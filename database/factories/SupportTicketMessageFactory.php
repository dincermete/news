<?php

namespace Database\Factories;

use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupportTicketMessage>
 */
class SupportTicketMessageFactory extends Factory
{
    protected $model = SupportTicketMessage::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'support_ticket_id' => SupportTicket::factory(),
            'user_id' => User::factory(),
            'body' => fake()->paragraph(),
            'is_staff' => false,
        ];
    }

    public function staff(?User $user = null): static
    {
        return $this->state(fn (): array => [
            'user_id' => $user?->id ?? User::factory()->admin(),
            'is_staff' => true,
        ]);
    }
}
