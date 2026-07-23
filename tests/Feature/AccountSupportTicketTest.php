<?php

namespace Tests\Feature;

use App\Enums\SupportTicketSource;
use App\Enums\SupportTicketStatus;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountSupportTicketTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_support_ticket_from_panel(): void
    {
        $user = User::factory()->customer()->create();

        $this->actingAs($user)
            ->post(route('account.support-tickets.store'), [
                'subject' => 'Ödeme sorunu',
                'body' => 'Havale yattı görünmüyor.',
            ])
            ->assertRedirect(route('account.support-tickets'));

        $this->assertDatabaseHas(SupportTicket::class, [
            'user_id' => $user->id,
            'subject' => 'Ödeme sorunu',
            'status' => SupportTicketStatus::Open->value,
            'source' => SupportTicketSource::Manual->value,
        ]);
    }

    public function test_support_tickets_can_be_filtered_by_status(): void
    {
        $user = User::factory()->customer()->create();

        SupportTicket::factory()->create([
            'user_id' => $user->id,
            'subject' => 'Açık talep',
            'status' => SupportTicketStatus::Open,
        ]);
        SupportTicket::factory()->create([
            'user_id' => $user->id,
            'subject' => 'Kapalı talep',
            'status' => SupportTicketStatus::Closed,
        ]);

        $this->actingAs($user)
            ->get(route('account.support-tickets', ['status' => 'closed']))
            ->assertOk()
            ->assertSee('Kapalı talep')
            ->assertDontSee('Açık talep');
    }
}
