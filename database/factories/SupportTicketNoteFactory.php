<?php

namespace Database\Factories;

use App\Models\SupportTicket;
use App\Models\SupportTicketNote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupportTicketNote>
 */
class SupportTicketNoteFactory extends Factory
{
    protected $model = SupportTicketNote::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'support_ticket_id' => SupportTicket::factory(),
            'admin_id' => User::factory()->admin(),
            'body' => fake()->sentence(),
        ];
    }
}
