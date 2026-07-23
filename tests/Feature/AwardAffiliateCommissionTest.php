<?php

namespace Tests\Feature;

use App\Enums\Currency;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\WalletBalanceType;
use App\Enums\WalletTransactionType;
use App\Jobs\ProcessSuccessfulPayment;
use App\Models\AffiliateCommission;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTopupPackage;
use App\Models\WalletTransaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AwardAffiliateCommissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Pdf::shouldReceive('loadView')->andReturnSelf();
        Pdf::shouldReceive('output')->andReturn('%PDF-fake');
    }

    public function test_awards_commission_when_referrer_has_rate(): void
    {
        $referrer = User::factory()->customer()->create([
            'affiliate_code' => 'REFCODE1',
            'affiliate_commission_rate' => 10,
        ]);

        $buyer = User::factory()->customer()->create([
            'referred_by_id' => $referrer->id,
        ]);

        $order = Order::factory()->create([
            'user_id' => $buyer->id,
            'price' => 200,
            'currency' => Currency::Try,
        ]);

        $payment = Payment::factory()->paid()->create([
            'order_id' => $order->id,
            'amount' => 200,
            'method' => PaymentMethod::Card,
            'status' => PaymentStatus::Paid,
            'wallet_topup_package_id' => null,
            'custom_topup_amount' => null,
        ]);

        (new ProcessSuccessfulPayment($payment))->handle();

        $this->assertDatabaseHas(AffiliateCommission::class, [
            'referrer_id' => $referrer->id,
            'referred_user_id' => $buyer->id,
            'order_id' => $order->id,
            'amount' => 20.00,
            'status' => 'approved',
        ]);

        $wallet = Wallet::forUser($referrer, Currency::Try);

        $this->assertSame(20.0, $wallet->bucketBalance(WalletBalanceType::AffiliateCommission));

        $this->assertDatabaseHas(WalletTransaction::class, [
            'wallet_id' => $wallet->id,
            'type' => WalletTransactionType::Credit->value,
            'amount' => 20.00,
            'reason' => 'affiliate_commission',
            'balance_type' => WalletBalanceType::AffiliateCommission->value,
            'related_order_id' => $order->id,
            'related_payment_id' => $payment->id,
        ]);
    }

    public function test_skips_when_referrer_has_no_commission_rate(): void
    {
        $referrer = User::factory()->customer()->create([
            'affiliate_code' => 'REFCODE2',
            'affiliate_commission_rate' => null,
        ]);

        $buyer = User::factory()->customer()->create([
            'referred_by_id' => $referrer->id,
        ]);

        $order = Order::factory()->create([
            'user_id' => $buyer->id,
            'price' => 150,
            'currency' => Currency::Try,
        ]);

        $payment = Payment::factory()->paid()->create([
            'order_id' => $order->id,
            'amount' => 150,
            'wallet_topup_package_id' => null,
            'custom_topup_amount' => null,
        ]);

        (new ProcessSuccessfulPayment($payment))->handle();

        $this->assertDatabaseMissing(AffiliateCommission::class, [
            'order_id' => $order->id,
        ]);

        $wallet = Wallet::forUser($referrer, Currency::Try);
        $this->assertSame(0.0, $wallet->bucketBalance(WalletBalanceType::AffiliateCommission));
    }

    public function test_wallet_topup_payments_do_not_earn_affiliate_commission(): void
    {
        $referrer = User::factory()->customer()->create([
            'affiliate_code' => 'REFCODE3',
            'affiliate_commission_rate' => 15,
        ]);

        $buyer = User::factory()->customer()->create([
            'referred_by_id' => $referrer->id,
        ]);

        $order = Order::factory()->create([
            'user_id' => $buyer->id,
            'price' => 100,
            'currency' => Currency::Try,
        ]);

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

        $this->assertDatabaseMissing(AffiliateCommission::class, [
            'order_id' => $order->id,
        ]);

        $wallet = Wallet::forUser($referrer, Currency::Try);
        $this->assertSame(0.0, $wallet->bucketBalance(WalletBalanceType::AffiliateCommission));
    }

    public function test_award_is_idempotent_per_order(): void
    {
        $referrer = User::factory()->customer()->create([
            'affiliate_commission_rate' => 10,
        ]);

        $buyer = User::factory()->customer()->create([
            'referred_by_id' => $referrer->id,
        ]);

        $order = Order::factory()->create([
            'user_id' => $buyer->id,
            'price' => 100,
            'currency' => Currency::Try,
        ]);

        $payment = Payment::factory()->paid()->create([
            'order_id' => $order->id,
            'amount' => 100,
            'wallet_topup_package_id' => null,
            'custom_topup_amount' => null,
        ]);

        (new ProcessSuccessfulPayment($payment))->handle();
        (new ProcessSuccessfulPayment($payment))->handle();

        $this->assertSame(1, AffiliateCommission::query()->where('order_id', $order->id)->count());

        $wallet = Wallet::forUser($referrer, Currency::Try);
        $this->assertSame(10.0, $wallet->bucketBalance(WalletBalanceType::AffiliateCommission));
    }
}
