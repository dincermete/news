<?php

namespace Tests\Feature;

use App\Enums\Currency;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\SpinCreditTransactionType;
use App\Enums\WalletBalanceType;
use App\Jobs\CreditWalletTopupBalance;
use App\Models\Order;
use App\Models\OrderGroup;
use App\Models\Payment;
use App\Models\SpinCreditTransaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\InstantOrderRefundService;
use App\Services\PaymentCompletionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class InstantOrderRefundTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Pdf::shouldReceive('loadView')->andReturnSelf();
        Pdf::shouldReceive('output')->andReturn('%PDF-fake');
    }

    public function test_refund_claws_back_wallet_then_marks_refunded(): void
    {
        [$user, $order, $payment] = $this->completedTopup(200);

        app(InstantOrderRefundService::class)->refund($order->fresh());

        $this->assertSame(OrderStatus::Refunded, $order->fresh()->status);
        $this->assertSame(0.0, Wallet::forUser($user, Currency::Try)->totalAvailableBalance());
        $this->assertDatabaseHas(WalletTransaction::class, [
            'related_order_id' => $order->id,
            'reason' => InstantOrderRefundService::WALLET_REFUND_REASON,
            'type' => 'debit',
        ]);
    }

    public function test_refund_fails_and_keeps_completed_when_balance_insufficient(): void
    {
        [$user, $order] = $this->completedTopup(150);

        $wallet = Wallet::forUser($user, Currency::Try);
        $wallet->debit(150, 'spent', balanceType: WalletBalanceType::Main);

        try {
            app(InstantOrderRefundService::class)->refund($order->fresh());
            $this->fail('Expected RuntimeException');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('yetersiz', mb_strtolower($e->getMessage()));
        }

        $this->assertSame(OrderStatus::Completed, $order->fresh()->status);
        $this->assertDatabaseMissing(WalletTransaction::class, [
            'related_order_id' => $order->id,
            'reason' => InstantOrderRefundService::WALLET_REFUND_REASON,
        ]);
    }

    public function test_refund_claws_back_spin_credits(): void
    {
        [$user, $order, $payment] = $this->completedTopup(300);

        $this->assertSame(9, $user->fresh()->spinCreditBalance());

        app(InstantOrderRefundService::class)->refund($order->fresh());

        $this->assertSame(0, $user->fresh()->spinCreditBalance());
        $this->assertDatabaseHas(SpinCreditTransaction::class, [
            'related_payment_id' => $payment->id,
            'type' => SpinCreditTransactionType::Debit->value,
            'reason' => InstantOrderRefundService::SPIN_REFUND_REASON,
            'amount' => 9,
        ]);
    }

    /**
     * @return array{0: User, 1: Order, 2: Payment}
     */
    protected function completedTopup(float $amount): array
    {
        Queue::fake();

        $user = User::factory()->create();
        $group = OrderGroup::factory()->create(['user_id' => $user->id, 'total' => $amount]);
        $order = Order::factory()->balanceTopup()->create([
            'user_id' => $user->id,
            'order_group_id' => $group->id,
            'price' => $amount,
            'currency' => Currency::Try,
            'status' => OrderStatus::PaymentPending,
        ]);
        $payment = Payment::factory()->create([
            'order_id' => null,
            'order_group_id' => $group->id,
            'amount' => $amount,
            'method' => PaymentMethod::BankTransfer,
            'status' => PaymentStatus::Notified,
        ]);

        app(PaymentCompletionService::class)->complete($payment);

        $this->assertSame(OrderStatus::Completed, $order->fresh()->status);
        $this->assertDatabaseHas(WalletTransaction::class, [
            'related_order_id' => $order->id,
            'reason' => CreditWalletTopupBalance::REASON,
        ]);

        return [$user, $order->fresh(), $payment->fresh()];
    }
}
