<?php

namespace Tests\Feature;

use App\Enums\Currency;
use App\Enums\CustomerStatus;
use App\Enums\WalletBalanceType;
use App\Models\User;
use App\Models\Wallet;
use App\Services\WalletAdjustmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WalletAdjustmentServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_positive_amount_credits_bucket(): void
    {
        $customer = User::factory()->customer()->create();
        $admin = User::factory()->admin()->create(['name' => 'Admin Ada']);

        $tx = app(WalletAdjustmentService::class)->adjust(
            $customer,
            WalletBalanceType::Main,
            150.5,
            'Promosyon',
            $admin,
        );

        $wallet = Wallet::forUser($customer, Currency::Try);

        $this->assertSame(150.5, $wallet->bucketBalance(WalletBalanceType::Main));
        $this->assertStringContainsString('manual_adjustment: Admin Ada: Promosyon', $tx->reason);
    }

    public function test_negative_amount_debits_bucket(): void
    {
        $customer = User::factory()->customer()->create();
        $admin = User::factory()->admin()->create();
        $wallet = Wallet::forUser($customer, Currency::Try);
        $wallet->credit(200, 'seed', balanceType: WalletBalanceType::Bonus);

        app(WalletAdjustmentService::class)->adjust(
            $customer,
            WalletBalanceType::Bonus,
            -50,
            'Düzeltme',
            $admin,
        );

        $this->assertSame(150.0, $wallet->fresh()->bucketBalance(WalletBalanceType::Bonus));
    }

    public function test_concurrent_adjustments_keep_consistent_balance(): void
    {
        $customer = User::factory()->customer()->create();
        $admin = User::factory()->admin()->create();
        $wallet = Wallet::forUser($customer, Currency::Try);
        $wallet->credit(100, 'seed', balanceType: WalletBalanceType::Main);

        $service = app(WalletAdjustmentService::class);

        DB::transaction(function () use ($service, $customer, $admin): void {
            $service->adjust($customer, WalletBalanceType::Main, 25, 'a', $admin);
            $service->adjust($customer, WalletBalanceType::Main, -10, 'b', $admin);
        });

        $this->assertSame(115.0, $wallet->fresh()->bucketBalance(WalletBalanceType::Main));
        $this->assertSame(115.0, $wallet->fresh()->totalAvailableBalance());
    }
}
