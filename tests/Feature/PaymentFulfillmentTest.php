<?php

namespace Tests\Feature;

use App\Enums\Currency;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ProductType;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Payments\Pages\ListPayments;
use App\Jobs\CreditWalletTopupBalance;
use App\Models\Order;
use App\Models\OrderGroup;
use App\Models\Payment;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\PaymentCompletionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class PaymentFulfillmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Pdf::shouldReceive('loadView')->andReturnSelf();
        Pdf::shouldReceive('output')->andReturn('%PDF-fake');
    }

    public function test_complete_marks_balance_order_completed_and_credits_wallet(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $group = OrderGroup::factory()->create(['user_id' => $user->id, 'total' => 250]);
        $order = Order::factory()->balanceTopup()->create([
            'user_id' => $user->id,
            'order_group_id' => $group->id,
            'price' => 250,
            'currency' => Currency::Try,
            'status' => OrderStatus::PaymentPending,
        ]);
        $payment = Payment::factory()->create([
            'order_id' => null,
            'order_group_id' => $group->id,
            'amount' => 250,
            'method' => PaymentMethod::BankTransfer,
            'status' => PaymentStatus::Notified,
        ]);

        app(PaymentCompletionService::class)->complete($payment);

        $this->assertSame(PaymentStatus::Paid, $payment->fresh()->status);
        $this->assertSame(OrderStatus::Completed, $order->fresh()->status);
        $this->assertSame(250.0, Wallet::forUser($user, Currency::Try)->totalAvailableBalance());
        $this->assertDatabaseHas(WalletTransaction::class, [
            'related_order_id' => $order->id,
            'reason' => CreditWalletTopupBalance::REASON,
        ]);
    }

    public function test_complete_is_idempotent_and_does_not_double_credit(): void
    {
        $user = User::factory()->create();
        $group = OrderGroup::factory()->create(['user_id' => $user->id, 'total' => 100]);
        $order = Order::factory()->balanceTopup()->create([
            'user_id' => $user->id,
            'order_group_id' => $group->id,
            'price' => 100,
            'status' => OrderStatus::PaymentPending,
        ]);
        $payment = Payment::factory()->create([
            'order_id' => null,
            'order_group_id' => $group->id,
            'amount' => 100,
            'method' => PaymentMethod::BankTransfer,
            'status' => PaymentStatus::Pending,
        ]);

        Queue::fake();

        $service = app(PaymentCompletionService::class);
        $service->complete($payment);

        $this->assertSame(PaymentStatus::Paid, $payment->fresh()->status);
        $this->assertSame(OrderStatus::Completed, $order->fresh()->status);
        $this->assertSame(100.0, Wallet::forUser($user, Currency::Try)->totalAvailableBalance());

        $service->complete($payment->fresh());
        $service->complete($payment->fresh());

        $this->assertSame(100.0, Wallet::forUser($user, Currency::Try)->totalAvailableBalance());
        $this->assertSame(1, WalletTransaction::query()
            ->where('related_order_id', $order->id)
            ->where('reason', CreditWalletTopupBalance::REASON)
            ->count());
    }

    public function test_instant_order_cannot_transition_content_pending_to_completed_via_public_api(): void
    {
        $order = Order::factory()->balanceTopup()->status(OrderStatus::ContentPending)->create();

        $this->assertFalse($order->canTransitionTo(OrderStatus::Completed));
        $this->assertFalse($order->transitionTo(OrderStatus::Completed));
        $this->assertSame(OrderStatus::ContentPending, $order->fresh()->status);
    }

    public function test_admin_bank_approve_credits_balance_topup_synchronously(): void
    {
        Queue::fake();

        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $group = OrderGroup::factory()->create(['user_id' => $user->id, 'total' => 180]);
        $order = Order::factory()->balanceTopup()->create([
            'user_id' => $user->id,
            'order_group_id' => $group->id,
            'price' => 180,
            'currency' => Currency::Try,
            'status' => OrderStatus::PaymentPending,
        ]);
        $payment = Payment::factory()->pendingBankTransfer()->create([
            'order_id' => null,
            'order_group_id' => $group->id,
            'amount' => 180,
            'currency' => Currency::Try,
        ]);

        $this->actingAs($admin);

        Livewire::test(ListPayments::class)
            ->callTableAction('approveBankTransfer', $payment)
            ->assertNotified()
            ->assertHasNoActionErrors();

        $this->assertSame(PaymentStatus::Paid, $payment->fresh()->status);
        $this->assertSame(OrderStatus::Completed, $order->fresh()->status);
        $this->assertSame(180.0, Wallet::forUser($user, Currency::Try)->totalAvailableBalance());
        $this->assertDatabaseHas(WalletTransaction::class, [
            'related_order_id' => $order->id,
            'reason' => CreditWalletTopupBalance::REASON,
        ]);
    }

    public function test_instant_order_hides_content_actions_and_gates_cancel_refund(): void
    {
        $admin = User::factory()->admin()->create();
        $pending = Order::factory()->balanceTopup()->status(OrderStatus::PaymentPending)->create();
        $completed = Order::factory()->balanceTopup()->status(OrderStatus::Completed)->create();

        $this->actingAs($admin);

        Livewire::test(ListOrders::class)
            ->assertTableActionHidden('approveContent', $pending)
            ->assertTableActionHidden('queueForPublish', $pending)
            ->assertTableActionVisible('cancel', $pending)
            ->assertTableActionHidden('refund', $pending)
            ->assertTableActionHidden('cancel', $completed)
            ->assertTableActionVisible('refund', $completed);
    }

    public function test_service_track_skips_review_step_in_public_transitions(): void
    {
        $order = new Order([
            'product_type' => ProductType::SeoPackage,
            'status' => OrderStatus::ContentPending,
        ]);

        $this->assertTrue($order->canTransitionTo(OrderStatus::InQueue));
        $this->assertFalse($order->canTransitionTo(OrderStatus::Review));
        $this->assertContains(OrderStatus::InQueue, $order->allowedTransitions());
    }

    public function test_backfill_command_completes_stuck_instant_orders_with_ledger(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $group = OrderGroup::factory()->create(['user_id' => $user->id, 'total' => 90]);
        $order = Order::factory()->balanceTopup()->create([
            'user_id' => $user->id,
            'order_group_id' => $group->id,
            'price' => 90,
            'currency' => Currency::Try,
            'status' => OrderStatus::ContentPending,
        ]);
        Payment::factory()->paid()->create([
            'order_id' => null,
            'order_group_id' => $group->id,
            'amount' => 90,
            'currency' => Currency::Try,
            'method' => PaymentMethod::BankTransfer,
        ]);

        $exit = Artisan::call('orders:complete-stuck-instant-topups');
        $this->assertSame(0, $exit, Artisan::output());

        $this->assertSame(OrderStatus::Completed, $order->fresh()->status);
        $this->assertSame(90.0, Wallet::forUser($user, Currency::Try)->totalAvailableBalance());
    }
}
