<?php

namespace Tests\Feature;

use App\Enums\ApiTokenAbility;
use App\Enums\Currency;
use App\Enums\WalletBalanceType;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiV1WalletTest extends TestCase
{
    use RefreshDatabase;

    public function test_wallet_balance_requires_ability(): void
    {
        $user = User::factory()->customer()->create();
        $token = $user->createToken('no-wallet', [ApiTokenAbility::ReadCatalog->value])->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/wallet/balance')
            ->assertForbidden();
    }

    public function test_wallet_balance_returns_buckets_and_total(): void
    {
        $user = User::factory()->customer()->create();
        $wallet = Wallet::forUser($user, Currency::Try);
        $wallet->credit(100, 'test main', balanceType: WalletBalanceType::Main);
        $wallet->credit(25, 'test bonus', balanceType: WalletBalanceType::Bonus);

        $token = $user->createToken('wallet', [ApiTokenAbility::ReadWallet->value])->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/wallet/balance')
            ->assertOk()
            ->assertJsonPath('data.total_available', 125)
            ->assertJsonPath('data.buckets.main', 100)
            ->assertJsonPath('data.buckets.bonus', 25);
    }
}
