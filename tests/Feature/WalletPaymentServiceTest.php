<?php

namespace Tests\Feature;

use App\Enums\Currency;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\WalletBalanceType;
use App\Enums\WalletTransactionType;
use App\Exceptions\InsufficientWalletBalanceException;
use App\Jobs\ProcessSuccessfulPayment;
use App\Models\Order;
use App\Models\OrderGroup;
use App\Models\Payment;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\WalletPaymentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WalletPaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Pdf::shouldReceive('loadView')->andReturnSelf();
        Pdf::shouldReceive('output')->andReturn('%PDF-fake');
    }

    public function test_pay_with_wallet_drains_buckets_in_priority_order(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $wallet = Wallet::forUser($user, Currency::Try);

        $wallet->credit(30, 'seed_bonus', balanceType: WalletBalanceType::Bonus);
        $wallet->credit(40, 'seed_spin', balanceType: WalletBalanceType::SpinPrize);
        $wallet->credit(20, 'seed_affiliate', balanceType: WalletBalanceType::AffiliateCommission);
        $wallet->credit(50, 'seed_main', balanceType: WalletBalanceType::Main);

        $order = Order::factory()->status(OrderStatus::PaymentPending)->create([
            'user_id' => $user->id,
            'price' => 100,
            'currency' => Currency::Try,
        ]);

        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'order_group_id' => null,
            'amount' => 100,
            'currency' => Currency::Try,
            'method' => PaymentMethod::Balance,
            'status' => PaymentStatus::Pending,
        ]);

        app(WalletPaymentService::class)->payWithWallet($payment);

        $this->assertSame(PaymentStatus::Paid, $payment->fresh()->status);
        $this->assertSame(OrderStatus::ContentPending, $order->fresh()->status);

        $this->assertSame(0.0, $wallet->fresh()->bucketBalance(WalletBalanceType::Bonus));
        $this->assertSame(0.0, $wallet->fresh()->bucketBalance(WalletBalanceType::SpinPrize));
        $this->assertSame(0.0, $wallet->fresh()->bucketBalance(WalletBalanceType::AffiliateCommission));
        $this->assertSame(40.0, $wallet->fresh()->bucketBalance(WalletBalanceType::Main));
        $this->assertSame(40.0, $wallet->fresh()->totalAvailableBalance());

        $debits = WalletTransaction::query()
            ->where('wallet_id', $wallet->id)
            ->where('type', WalletTransactionType::Debit)
            ->where('reason', 'payment')
            ->where('related_payment_id', $payment->id)
            ->orderBy('id')
            ->get();

        $this->assertCount(4, $debits);
        $this->assertSame(WalletBalanceType::Bonus, $debits[0]->balance_type);
        $this->assertSame('30.00', $debits[0]->amount);
        $this->assertSame(WalletBalanceType::SpinPrize, $debits[1]->balance_type);
        $this->assertSame('40.00', $debits[1]->amount);
        $this->assertSame(WalletBalanceType::AffiliateCommission, $debits[2]->balance_type);
        $this->assertSame('20.00', $debits[2]->amount);
        $this->assertSame(WalletBalanceType::Main, $debits[3]->balance_type);
        $this->assertSame('10.00', $debits[3]->amount);

        Queue::assertPushed(ProcessSuccessfulPayment::class);
    }

    public function test_pay_with_wallet_throws_when_balance_is_insufficient(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::forUser($user, Currency::Try);
        $wallet->credit(25, 'seed', balanceType: WalletBalanceType::Main);

        $order = Order::factory()->status(OrderStatus::PaymentPending)->create([
            'user_id' => $user->id,
            'price' => 100,
        ]);

        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'amount' => 100,
            'method' => PaymentMethod::Balance,
            'status' => PaymentStatus::Pending,
        ]);

        try {
            app(WalletPaymentService::class)->payWithWallet($payment);
            $this->fail('Expected InsufficientWalletBalanceException');
        } catch (InsufficientWalletBalanceException $exception) {
            $this->assertSame('Bakiyeniz bu siparişi karşılamaya yeterli değil', $exception->getMessage());
        }

        $this->assertSame(PaymentStatus::Pending, $payment->fresh()->status);
        $this->assertSame(25.0, $wallet->fresh()->totalAvailableBalance());
        $this->assertSame(0, WalletTransaction::query()->where('type', WalletTransactionType::Debit)->count());
    }

    public function test_concurrent_wallet_payments_do_not_drive_balance_negative(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $wallet = Wallet::forUser($user, Currency::Try);
        $wallet->credit(100, 'seed', balanceType: WalletBalanceType::Main);

        $orderGroup = OrderGroup::factory()->create([
            'user_id' => $user->id,
            'total' => 100,
            'currency' => Currency::Try,
        ]);

        Order::factory()->status(OrderStatus::PaymentPending)->create([
            'user_id' => $user->id,
            'order_group_id' => $orderGroup->id,
            'price' => 100,
        ]);

        $first = Payment::factory()->create([
            'order_id' => null,
            'order_group_id' => $orderGroup->id,
            'amount' => 100,
            'currency' => Currency::Try,
            'method' => PaymentMethod::Balance,
            'status' => PaymentStatus::Pending,
        ]);

        $secondGroup = OrderGroup::factory()->create([
            'user_id' => $user->id,
            'total' => 100,
            'currency' => Currency::Try,
        ]);

        Order::factory()->status(OrderStatus::PaymentPending)->create([
            'user_id' => $user->id,
            'order_group_id' => $secondGroup->id,
            'price' => 100,
        ]);

        $second = Payment::factory()->create([
            'order_id' => null,
            'order_group_id' => $secondGroup->id,
            'amount' => 100,
            'currency' => Currency::Try,
            'method' => PaymentMethod::Balance,
            'status' => PaymentStatus::Pending,
        ]);

        $service = app(WalletPaymentService::class);
        $service->payWithWallet($first);

        try {
            $service->payWithWallet($second);
            $this->fail('Expected InsufficientWalletBalanceException on second payment');
        } catch (InsufficientWalletBalanceException $exception) {
            $this->assertSame('Bakiyeniz bu siparişi karşılamaya yeterli değil', $exception->getMessage());
        }

        $this->assertSame(0.0, $wallet->fresh()->totalAvailableBalance());
        $this->assertGreaterThanOrEqual(0, $wallet->fresh()->totalAvailableBalance());
        $this->assertSame(PaymentStatus::Paid, $first->fresh()->status);
        $this->assertSame(PaymentStatus::Pending, $second->fresh()->status);
    }
}
