<?php

namespace Tests\Feature;

use App\Enums\Currency;
use App\Enums\OrderStatus;
use App\Enums\SiteStatus;
use App\Enums\SpinPrizeType;
use App\Enums\WalletBalanceType;
use App\Models\AffiliateCommission;
use App\Models\Order;
use App\Models\Site;
use App\Models\SpinWheelPrize;
use App\Models\SpinWheelSpin;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('account.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_dashboard_shows_wallet_orders_and_spin_stats(): void
    {
        $user = User::factory()->customer()->create();
        $wallet = Wallet::forUser($user, Currency::Try);
        $wallet->credit(120, 'seed', balanceType: WalletBalanceType::Main);
        $wallet->credit(30, 'bonus', balanceType: WalletBalanceType::Bonus);

        Order::factory()->create([
            'user_id' => $user->id,
            'site_id' => Site::factory()->create(['status' => SiteStatus::Active]),
            'status' => OrderStatus::Published,
            'price' => 99,
        ]);

        $prize = SpinWheelPrize::factory()->create([
            'type' => SpinPrizeType::Balance,
            'value' => 25,
        ]);
        SpinWheelSpin::factory()->create([
            'user_id' => $user->id,
            'spin_wheel_prize_id' => $prize->id,
        ]);

        $this->actingAs($user)
            ->get(route('account.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee('150,00')
            ->assertDontSee('Henüz siparişiniz yok.')
            ->assertSee('25,00')
            ->assertSee('Oran bekleniyor')
            ->assertDontSee('Faz 11e4')
            ->assertSee(route('account.affiliate'), false);
    }

    public function test_dashboard_affiliate_card_shows_earned_commission_when_rate_set(): void
    {
        $user = User::factory()->customer()->create([
            'affiliate_commission_rate' => 10,
        ]);

        AffiliateCommission::factory()->approved()->create([
            'referrer_id' => $user->id,
            'referred_user_id' => User::factory()->customer()->create()->id,
            'amount' => 45.50,
        ]);

        $this->actingAs($user)
            ->get(route('account.dashboard'))
            ->assertOk()
            ->assertSee('45,50')
            ->assertSee('10,00')
            ->assertDontSee('Faz 11e4');
    }

    public function test_dashboard_shows_empty_orders_message(): void
    {
        $user = User::factory()->customer()->create();

        $this->actingAs($user)
            ->get(route('account.dashboard'))
            ->assertOk()
            ->assertSee('Henüz siparişiniz yok.');
    }

    public function test_unverified_email_banner_is_shown(): void
    {
        $user = User::factory()->customer()->unverified()->create();

        $this->actingAs($user)
            ->get(route('account.dashboard'))
            ->assertOk()
            ->assertSee('E-posta adresiniz doğrulanmadı');
    }
}
