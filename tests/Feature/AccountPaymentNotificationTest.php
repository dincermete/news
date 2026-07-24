<?php

namespace Tests\Feature;

use App\Enums\Currency;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\BankAccount;
use App\Models\OrderGroup;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountPaymentNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_notification_page_lists_banks_and_pending_payments(): void
    {
        BankAccount::factory()->create([
            'name' => 'Ziraat Bankası',
            'iban' => 'TR00 0000 0000 0000 0000 0000 00',
            'account_name' => 'NewsTanıtım',
        ]);

        $user = User::factory()->customer()->create();
        $group = OrderGroup::factory()->create(['user_id' => $user->id, 'total' => 100]);

        Payment::factory()->create([
            'order_id' => null,
            'order_group_id' => $group->id,
            'amount' => 100,
            'currency' => Currency::Try,
            'method' => PaymentMethod::BankTransfer,
            'status' => PaymentStatus::Pending,
        ]);

        $this->actingAs($user)
            ->get(route('account.payment-notification'))
            ->assertOk()
            ->assertSee('Ziraat Bankası')
            ->assertSee('Ödeme Bildirimi Yap')
            ->assertSee('100,00');
    }

    public function test_user_can_submit_bank_transfer_notification_from_account(): void
    {
        $user = User::factory()->customer()->create();
        $group = OrderGroup::factory()->create(['user_id' => $user->id]);
        $payment = Payment::factory()->create([
            'order_id' => null,
            'order_group_id' => $group->id,
            'method' => PaymentMethod::BankTransfer,
            'status' => PaymentStatus::Pending,
            'amount' => 80,
        ]);

        $this->actingAs($user)
            ->post(route('payment.bank-transfer-notify'), [
                'payment_id' => $payment->id,
                'bank_name' => 'Ziraat Bankası',
                'payer_name' => $user->name,
                'payer_note' => 'Sipariş ödemesi',
            ])
            ->assertRedirect(route('checkout.success', $group));

        $this->assertDatabaseHas(Payment::class, [
            'id' => $payment->id,
            'status' => PaymentStatus::Notified->value,
            'bank_name' => 'Ziraat Bankası',
            'payer_name' => $user->name,
        ]);
    }
}
