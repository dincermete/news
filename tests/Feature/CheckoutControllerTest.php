<?php

namespace Tests\Feature;

use App\Enums\CartStatus;
use App\Enums\Currency;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\SiteStatus;
use App\Enums\WalletBalanceType;
use App\Models\BankAccount;
use App\Models\BillingProfile;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\OrderGroup;
use App\Models\Page;
use App\Models\Payment;
use App\Models\Site;
use App\Models\User;
use App\Models\Wallet;
use App\Services\PaytrService;
use App\Services\WalletPaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Mockery\MockInterface;
use Tests\TestCase;

class CheckoutControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function seedCart(User $user, float $price = 100): Cart
    {
        $cart = Cart::factory()->create([
            'user_id' => $user->id,
            'status' => CartStatus::Active,
        ]);

        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'site_id' => Site::factory()->create(['status' => SiteStatus::Active]),
            'price' => $price,
            'currency' => Currency::Try,
            'configured_at' => now(),
        ]);

        return $cart;
    }

    public function test_guest_is_redirected_from_checkout(): void
    {
        $this->get(route('checkout.show'))
            ->assertRedirect(route('login'));
    }

    public function test_checkout_blocked_when_cart_has_unconfigured_item(): void
    {
        $user = User::factory()->create();
        $cart = Cart::factory()->create(['user_id' => $user->id, 'status' => CartStatus::Active]);

        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'site_id' => Site::factory()->create(['status' => SiteStatus::Active]),
            'price' => 100,
            'currency' => Currency::Try,
            'configured_at' => null,
        ]);

        $this->actingAs($user)
            ->get(route('checkout.show'))
            ->assertRedirect(route('cart.index'))
            ->assertSessionHasErrors('cart');

        $this->actingAs($user)
            ->from(route('cart.index'))
            ->post(route('checkout.process'), [
                'payment_method' => PaymentMethod::Card->value,
                'contracts_accepted' => '1',
            ])
            ->assertRedirect(route('cart.index'))
            ->assertSessionHasErrors('cart');

        $this->assertDatabaseCount('order_groups', 0);
    }

    public function test_process_requires_contract_acceptance(): void
    {
        $user = User::factory()->create();
        $billing = BillingProfile::factory()->create(['user_id' => $user->id]);
        $this->seedCart($user);

        $this->actingAs($user)
            ->from(route('checkout.show'))
            ->post(route('checkout.process'), [
                'billing_profile_id' => $billing->id,
                'payment_method' => PaymentMethod::Card->value,
            ])
            ->assertRedirect(route('checkout.show'))
            ->assertSessionHasErrors('contracts_accepted');
    }

    public function test_card_checkout_calls_paytr_service(): void
    {
        $user = User::factory()->create();
        $billing = BillingProfile::factory()->create(['user_id' => $user->id]);
        $this->seedCart($user, 100);

        $this->mock(PaytrService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('getIframeToken')
                ->once()
                ->andReturnUsing(function (Payment $payment): array {
                    $payment->forceFill(['paytr_token' => 'test-token'])->save();

                    return [
                        'token' => 'test-token',
                        'merchant_oid' => 'GRPTEST',
                        'payment' => $payment,
                    ];
                });
        });

        $this->actingAs($user)
            ->post(route('checkout.process'), [
                'billing_profile_id' => $billing->id,
                'payment_method' => PaymentMethod::Card->value,
                'contracts_accepted' => '1',
            ])
            ->assertOk()
            ->assertSee('paytriframe')
            ->assertSee('test-token');

        $this->assertDatabaseHas(OrderGroup::class, [
            'user_id' => $user->id,
            'total' => 100,
        ]);

        $this->assertDatabaseHas(Payment::class, [
            'method' => PaymentMethod::Card->value,
            'status' => PaymentStatus::Pending->value,
            'amount' => 100,
        ]);
    }

    public function test_bank_transfer_checkout_applies_two_percent_discount_and_shows_banks(): void
    {
        config(['payment.bank_transfer_discount_percent' => 2]);
        BankAccount::factory()->create(['name' => 'Ziraat Bankası']);

        $user = User::factory()->create();
        $billing = BillingProfile::factory()->create(['user_id' => $user->id]);
        $this->seedCart($user, 100);

        $this->actingAs($user)
            ->post(route('checkout.process'), [
                'billing_profile_id' => $billing->id,
                'payment_method' => PaymentMethod::BankTransfer->value,
                'contracts_accepted' => '1',
            ])
            ->assertOk()
            ->assertSee('Havale')
            ->assertSee('Ziraat')
            ->assertSee('havale-bildirimi', false);

        $this->assertDatabaseHas(Payment::class, [
            'method' => PaymentMethod::BankTransfer->value,
            'amount' => 98,
            'status' => PaymentStatus::Pending->value,
        ]);
    }

    public function test_checkout_succeeds_without_any_billing_info(): void
    {
        $user = User::factory()->create();
        $this->seedCart($user, 100);

        $this->mock(PaytrService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('getIframeToken')
                ->once()
                ->andReturnUsing(function (Payment $payment): array {
                    $payment->forceFill(['paytr_token' => 'test-token'])->save();

                    return [
                        'token' => 'test-token',
                        'merchant_oid' => 'GRPTEST',
                        'payment' => $payment,
                    ];
                });
        });

        $this->actingAs($user)
            ->post(route('checkout.process'), [
                'payment_method' => PaymentMethod::Card->value,
                'contracts_accepted' => '1',
            ])
            ->assertOk();

        $group = OrderGroup::query()->where('user_id', $user->id)->first();

        $this->assertNotNull($group);
        $this->assertNull($group->billing_profile_id);
    }

    public function test_balance_checkout_calls_wallet_payment_service(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $billing = BillingProfile::factory()->create(['user_id' => $user->id]);
        $this->seedCart($user, 100);

        $wallet = Wallet::forUser($user, Currency::Try);
        $wallet->credit(150, 'seed', balanceType: WalletBalanceType::Main);

        $this->mock(WalletPaymentService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('payWithWallet')
                ->once()
                ->andReturnUsing(function (Payment $payment): void {
                    $payment->forceFill([
                        'status' => PaymentStatus::Paid,
                        'paid_at' => now(),
                    ])->save();
                });
        });

        $response = $this->actingAs($user)
            ->post(route('checkout.process'), [
                'billing_profile_id' => $billing->id,
                'payment_method' => PaymentMethod::Balance->value,
                'contracts_accepted' => '1',
            ]);

        $group = OrderGroup::query()->where('user_id', $user->id)->first();
        $this->assertNotNull($group);
        $response->assertRedirect(route('checkout.success', $group));

        $this->assertDatabaseHas(Payment::class, [
            'order_group_id' => $group->id,
            'method' => PaymentMethod::Balance->value,
            'status' => PaymentStatus::Paid->value,
        ]);
    }

    public function test_balance_checkout_rejects_insufficient_funds_before_checkout(): void
    {
        $user = User::factory()->create();
        $billing = BillingProfile::factory()->create(['user_id' => $user->id]);
        $this->seedCart($user, 100);

        Wallet::forUser($user, Currency::Try)->credit(10, 'seed', balanceType: WalletBalanceType::Main);

        $this->mock(WalletPaymentService::class, function (MockInterface $mock): void {
            $mock->shouldNotReceive('payWithWallet');
        });

        $this->actingAs($user)
            ->from(route('checkout.show'))
            ->post(route('checkout.process'), [
                'billing_profile_id' => $billing->id,
                'payment_method' => PaymentMethod::Balance->value,
                'contracts_accepted' => '1',
            ])
            ->assertRedirect(route('checkout.show'))
            ->assertSessionHasErrors('payment_method')
            ->assertSessionHas('wallet_topup');

        $this->assertDatabaseCount('order_groups', 0);
    }

    public function test_checkout_show_lists_discounted_bank_transfer_amount(): void
    {
        config(['payment.bank_transfer_discount_percent' => 2]);

        $user = User::factory()->create();
        BillingProfile::factory()->create(['user_id' => $user->id]);
        $this->seedCart($user, 200);

        $this->actingAs($user)
            ->get(route('checkout.show'))
            ->assertOk()
            ->assertSee('196');
    }

    public function test_legal_page_route_serves_seeded_contract_page(): void
    {
        Page::factory()->create([
            'slug' => 'mesafeli-satis-sozlesmesi',
            'title' => 'Mesafeli Satış Sözleşmesi',
            'content' => '<p>İskelet</p>',
            'is_active' => true,
        ]);

        $this->get(route('pages.show', 'mesafeli-satis-sozlesmesi'))
            ->assertOk()
            ->assertSee('Mesafeli');
    }
}
