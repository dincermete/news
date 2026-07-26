<?php

namespace Tests\Feature;

use App\Enums\SupportTicketSource;
use App\Enums\SupportTicketStatus;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountSupportTicketTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_support_ticket_from_panel(): void
    {
        $user = User::factory()->customer()->create();

        $response = $this->actingAs($user)
            ->post(route('account.support-tickets.store'), [
                'subject' => 'Ödeme sorunu',
                'body' => 'Havale yattı görünmüyor.',
            ]);

        $ticket = SupportTicket::query()->where('subject', 'Ödeme sorunu')->first();

        $this->assertNotNull($ticket);
        $response->assertRedirect(route('account.support-tickets.show', $ticket));

        $this->assertDatabaseHas(SupportTicket::class, [
            'user_id' => $user->id,
            'subject' => 'Ödeme sorunu',
            'status' => SupportTicketStatus::Open->value,
            'source' => SupportTicketSource::Manual->value,
        ]);

        $this->assertDatabaseHas(SupportTicketMessage::class, [
            'support_ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'body' => 'Havale yattı görünmüyor.',
            'is_staff' => false,
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

    public function test_user_can_view_and_reply_to_own_ticket(): void
    {
        $user = User::factory()->customer()->create();
        $ticket = SupportTicket::factory()->create([
            'user_id' => $user->id,
            'subject' => 'Yanıt testi',
            'status' => SupportTicketStatus::InProgress,
        ]);

        $this->actingAs($user)
            ->get(route('account.support-tickets.show', $ticket))
            ->assertOk()
            ->assertSee('Yanıt testi');

        $this->actingAs($user)
            ->post(route('account.support-tickets.reply', $ticket), [
                'body' => 'Ek bilgi: dekont ekledim.',
            ])
            ->assertRedirect(route('account.support-tickets.show', $ticket));

        $this->assertDatabaseHas(SupportTicketMessage::class, [
            'support_ticket_id' => $ticket->id,
            'body' => 'Ek bilgi: dekont ekledim.',
            'is_staff' => false,
        ]);

        $this->assertSame(SupportTicketStatus::Open, $ticket->fresh()->status);
    }

    public function test_user_cannot_view_another_users_ticket(): void
    {
        $owner = User::factory()->customer()->create();
        $other = User::factory()->customer()->create();
        $ticket = SupportTicket::factory()->create([
            'user_id' => $owner->id,
        ]);

        $this->actingAs($other)
            ->get(route('account.support-tickets.show', $ticket))
            ->assertNotFound();
    }

    public function test_customer_reply_reopens_closed_ticket(): void
    {
        $user = User::factory()->customer()->create();
        $ticket = SupportTicket::factory()->create([
            'user_id' => $user->id,
            'status' => SupportTicketStatus::Closed,
            'closed_at' => now(),
        ]);

        $this->actingAs($user)
            ->post(route('account.support-tickets.reply', $ticket), [
                'body' => 'Hâlâ çözülmedi.',
            ])
            ->assertRedirect(route('account.support-tickets.show', $ticket));

        $ticket->refresh();
        $this->assertSame(SupportTicketStatus::Open, $ticket->status);
        $this->assertNull($ticket->closed_at);
    }

    public function test_staff_reply_creates_user_notification(): void
    {
        $customer = User::factory()->customer()->create();
        $staff = User::factory()->admin()->create();
        $ticket = SupportTicket::factory()->create([
            'user_id' => $customer->id,
            'status' => SupportTicketStatus::Open,
        ]);

        $ticket->addMessage($staff, 'Ödemeniz onaylandı.', isStaff: true);
        $ticket->markInProgress();

        UserNotification::query()->create([
            'user_id' => $customer->id,
            'title' => 'Destek talebinize yanıt geldi',
            'body' => '#'.$ticket->id.' — '.$ticket->subject,
        ]);

        $this->assertDatabaseHas(SupportTicketMessage::class, [
            'support_ticket_id' => $ticket->id,
            'is_staff' => true,
            'body' => 'Ödemeniz onaylandı.',
        ]);
        $this->assertDatabaseHas(UserNotification::class, [
            'user_id' => $customer->id,
            'title' => 'Destek talebinize yanıt geldi',
        ]);
        $this->assertSame(SupportTicketStatus::InProgress, $ticket->fresh()->status);
    }
}
