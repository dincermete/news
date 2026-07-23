<?php

namespace Tests\Feature;

use App\Enums\SpinCreditTransactionType;
use App\Exceptions\InsufficientSpinCreditsException;
use App\Models\SpinCreditTransaction;
use App\Models\SpinWheelPrize;
use App\Models\SpinWheelSpin;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\SpinWheelService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpinWheelServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_without_credits_cannot_spin(): void
    {
        $user = User::factory()->create();
        SpinWheelPrize::factory()->none()->create(['probability_weight' => 1]);

        $this->expectException(InsufficientSpinCreditsException::class);

        app(SpinWheelService::class)->spin($user);
    }

    public function test_spin_consumes_one_credit(): void
    {
        $user = User::factory()->create();
        SpinCreditTransaction::factory()->credit(3)->create(['user_id' => $user->id]);
        SpinWheelPrize::factory()->none()->create(['probability_weight' => 1]);

        app(SpinWheelService::class)->spin($user);

        $this->assertSame(2, $user->fresh()->spinCreditBalance());
        $this->assertDatabaseHas(SpinCreditTransaction::class, [
            'user_id' => $user->id,
            'type' => SpinCreditTransactionType::Debit->value,
            'amount' => 1,
            'reason' => 'spin_wheel_spin',
        ]);
        $this->assertDatabaseCount(SpinWheelSpin::class, 1);
    }

    public function test_stocked_prize_is_excluded_when_depleted(): void
    {
        $user = User::factory()->create();
        SpinCreditTransaction::factory()->credit(2)->create(['user_id' => $user->id]);

        $stocked = SpinWheelPrize::factory()->none()->withStock(1)->create([
            'name' => 'Stoklu',
            'probability_weight' => 1,
        ]);

        $service = app(SpinWheelService::class);

        $first = $service->spin($user);
        $this->assertSame($stocked->id, $first->spin_wheel_prize_id);
        $this->assertSame(0, $stocked->fresh()->stock);

        $fallback = SpinWheelPrize::factory()->none()->create([
            'name' => 'Sınırsız',
            'probability_weight' => 1,
            'stock' => null,
        ]);

        $second = $service->spin($user);
        $this->assertSame($fallback->id, $second->spin_wheel_prize_id);
        $this->assertSame(0, $stocked->fresh()->stock);
    }

    public function test_concurrent_spin_attempts_do_not_drive_balance_negative(): void
    {
        $user = User::factory()->create();
        SpinCreditTransaction::factory()->credit(3)->create(['user_id' => $user->id]);
        SpinWheelPrize::factory()->none()->create(['probability_weight' => 1]);

        $service = app(SpinWheelService::class);
        $succeeded = 0;
        $failed = 0;

        foreach (range(1, 10) as $attempt) {
            try {
                $service->spin($user);
                $succeeded++;
            } catch (InsufficientSpinCreditsException) {
                $failed++;
            }
        }

        $this->assertSame(3, $succeeded);
        $this->assertSame(7, $failed);
        $this->assertSame(0, $user->fresh()->spinCreditBalance());
        $this->assertGreaterThanOrEqual(0, $user->fresh()->spinCreditBalance());
        $this->assertDatabaseCount(SpinWheelSpin::class, 3);
    }

    public function test_balance_prize_credits_wallet(): void
    {
        $user = User::factory()->create();
        SpinCreditTransaction::factory()->credit(1)->create(['user_id' => $user->id]);
        SpinWheelPrize::factory()->balance(25)->create(['probability_weight' => 1]);

        app(SpinWheelService::class)->spin($user);

        $wallet = Wallet::query()->where('user_id', $user->id)->first();

        $this->assertNotNull($wallet);
        $this->assertSame('25.00', $wallet->balance);
        $this->assertDatabaseHas(WalletTransaction::class, [
            'wallet_id' => $wallet->id,
            'reason' => 'spin_wheel_prize',
            'amount' => 25,
        ]);
    }

    public function test_spin_credit_transactions_are_append_only(): void
    {
        $transaction = SpinCreditTransaction::factory()->credit(5)->create();

        $this->assertFalse($transaction->update(['amount' => 99]));
        $this->assertFalse($transaction->delete());

        $this->assertDatabaseHas(SpinCreditTransaction::class, [
            'id' => $transaction->id,
            'amount' => 5,
        ]);
    }
}
