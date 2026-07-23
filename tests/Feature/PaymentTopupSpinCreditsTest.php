<?php

namespace Tests\Feature;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\SpinCreditTransactionType;
use App\Jobs\AwardSpinCredits;
use App\Jobs\ProcessSuccessfulPayment;
use App\Models\Order;
use App\Models\Payment;
use App\Models\SpinCreditTransaction;
use App\Models\User;
use App\Models\WalletTopupPackage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentTopupSpinCreditsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Pdf::shouldReceive('loadView')->andReturnSelf();
        Pdf::shouldReceive('output')->andReturn('%PDF-fake');
    }

    public function test_package_topup_awards_configured_spin_credits(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'price' => 100]);
        $package = WalletTopupPackage::factory()->create([
            'amount' => 100,
            'spin_credits' => 3,
        ]);

        $payment = Payment::factory()->paid()->create([
            'order_id' => $order->id,
            'amount' => 100,
            'method' => PaymentMethod::Card,
            'status' => PaymentStatus::Paid,
            'wallet_topup_package_id' => $package->id,
            'custom_topup_amount' => null,
        ]);

        (new ProcessSuccessfulPayment($payment))->handle();

        $this->assertDatabaseHas(SpinCreditTransaction::class, [
            'user_id' => $user->id,
            'type' => SpinCreditTransactionType::Credit->value,
            'amount' => 3,
            'reason' => 'wallet_topup',
            'related_payment_id' => $payment->id,
        ]);
        $this->assertSame(3, $user->fresh()->spinCreditBalance());
    }

    public function test_custom_topup_awards_credits_with_floor_formula(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'price' => 250]);

        $payment = Payment::factory()->paid()->create([
            'order_id' => $order->id,
            'amount' => 250,
            'method' => PaymentMethod::Card,
            'status' => PaymentStatus::Paid,
            'wallet_topup_package_id' => null,
            'custom_topup_amount' => 250,
        ]);

        (new AwardSpinCredits($payment))->handle();

        // floor(250/100)*3 = 2*3 = 6
        $this->assertDatabaseHas(SpinCreditTransaction::class, [
            'user_id' => $user->id,
            'type' => SpinCreditTransactionType::Credit->value,
            'amount' => 6,
            'reason' => 'wallet_topup',
            'related_payment_id' => $payment->id,
        ]);
        $this->assertSame(6, $user->fresh()->spinCreditBalance());
    }

    public function test_custom_topup_formula_rounds_down(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $payment = Payment::factory()->paid()->create([
            'order_id' => $order->id,
            'wallet_topup_package_id' => null,
            'custom_topup_amount' => 199,
        ]);

        (new AwardSpinCredits($payment))->handle();

        // floor(199/100)*3 = 1*3 = 3
        $this->assertSame(3, $user->fresh()->spinCreditBalance());
    }

    public function test_regular_order_payment_does_not_award_spin_credits(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $payment = Payment::factory()->paid()->create([
            'order_id' => $order->id,
            'wallet_topup_package_id' => null,
            'custom_topup_amount' => null,
        ]);

        (new ProcessSuccessfulPayment($payment))->handle();

        $this->assertSame(0, $user->fresh()->spinCreditBalance());
        $this->assertDatabaseMissing(SpinCreditTransaction::class, [
            'related_payment_id' => $payment->id,
        ]);
    }

    public function test_award_spin_credits_is_idempotent_per_payment(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        $package = WalletTopupPackage::factory()->create(['spin_credits' => 15]);

        $payment = Payment::factory()->paid()->create([
            'order_id' => $order->id,
            'wallet_topup_package_id' => $package->id,
        ]);

        (new AwardSpinCredits($payment))->handle();
        (new AwardSpinCredits($payment))->handle();

        $this->assertSame(15, $user->fresh()->spinCreditBalance());
        $this->assertSame(1, SpinCreditTransaction::query()->where('related_payment_id', $payment->id)->count());
    }
}
