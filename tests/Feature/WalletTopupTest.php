<?php

namespace Tests\Feature;

use App\Enums\CartStatus;
use App\Enums\Currency;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ProductType;
use App\Jobs\ProcessSuccessfulPayment;
use App\Models\BillingProfile;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Payment;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTopupPackage;
use App\Services\CartCheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletTopupTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_add_a_wallet_topup_package_to_cart(): void
    {
        $user = User::factory()->create();
        $package = WalletTopupPackage::factory()->create([
            'amount' => 200,
            'spin_credits' => 6,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->post(route('cart.add'), [
                'product_type' => ProductType::Balance->value,
                'wallet_topup_package_id' => $package->id,
            ])
            ->assertRedirect(route('cart.index'));

        $this->assertDatabaseHas(CartItem::class, [
            'product_type' => ProductType::Balance->value,
            'wallet_topup_package_id' => $package->id,
            'price' => 200,
        ]);
    }

    public function test_user_can_add_a_custom_wallet_topup_amount_to_cart(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('cart.add'), [
                'product_type' => ProductType::Balance->value,
                'custom_topup_amount' => 350,
            ])
            ->assertRedirect(route('cart.index'));

        $this->assertDatabaseHas(CartItem::class, [
            'product_type' => ProductType::Balance->value,
            'wallet_topup_package_id' => null,
            'price' => 350,
        ]);
    }

    public function test_custom_topup_amount_below_minimum_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('cart.add'), [
                'product_type' => ProductType::Balance->value,
                'custom_topup_amount' => 10,
            ])
            ->assertSessionHasErrors('custom_topup_amount');

        $this->assertDatabaseMissing(CartItem::class, ['product_type' => ProductType::Balance->value]);
    }

    public function test_checkout_rejects_wallet_balance_payment_when_cart_has_topup_item(): void
    {
        $user = User::factory()->create();
        $billing = BillingProfile::factory()->create(['user_id' => $user->id]);
        $cart = Cart::factory()->create(['user_id' => $user->id, 'status' => CartStatus::Active]);

        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_type' => ProductType::Balance,
            'wallet_topup_package_id' => null,
            'price' => 200,
            'currency' => Currency::Try,
            'configured_at' => now(),
        ]);

        $wallet = Wallet::forUser($user, Currency::Try);
        $wallet->credit(500, 'test_seed');

        $this->actingAs($user)
            ->post(route('checkout.process'), [
                'billing_profile_id' => $billing->id,
                'payment_method' => PaymentMethod::Balance->value,
                'contracts_accepted' => '1',
            ])
            ->assertRedirect(route('checkout.show'))
            ->assertSessionHasErrors('payment_method');
    }

    public function test_paid_bank_transfer_payment_credits_wallet_and_awards_spin_credits_for_a_package(): void
    {
        $user = User::factory()->create();
        $billing = BillingProfile::factory()->create(['user_id' => $user->id]);
        $cart = Cart::factory()->create(['user_id' => $user->id, 'status' => CartStatus::Active]);
        $package = WalletTopupPackage::factory()->create(['amount' => 200, 'spin_credits' => 6]);

        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_type' => ProductType::Balance,
            'wallet_topup_package_id' => $package->id,
            'price' => 200,
            'currency' => Currency::Try,
            'configured_at' => now(),
        ]);

        $group = app(CartCheckoutService::class)->checkout($cart, $billing, method: PaymentMethod::BankTransfer);
        $payment = Payment::query()->where('order_group_id', $group->id)->firstOrFail();

        $payment->forceFill(['status' => PaymentStatus::Paid])->save();

        (new ProcessSuccessfulPayment($payment->fresh()))->handle();

        $wallet = Wallet::forUser($user, Currency::Try);
        $this->assertSame(200.0, $wallet->totalAvailableBalance());
        $this->assertSame(6, $user->fresh()->spinCreditBalance());
    }

    public function test_paid_bank_transfer_payment_credits_wallet_and_awards_spin_credits_for_a_custom_amount(): void
    {
        $user = User::factory()->create();
        $billing = BillingProfile::factory()->create(['user_id' => $user->id]);
        $cart = Cart::factory()->create(['user_id' => $user->id, 'status' => CartStatus::Active]);

        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_type' => ProductType::Balance,
            'wallet_topup_package_id' => null,
            'price' => 350,
            'currency' => Currency::Try,
            'configured_at' => now(),
        ]);

        $group = app(CartCheckoutService::class)->checkout($cart, $billing, method: PaymentMethod::BankTransfer);
        $payment = Payment::query()->where('order_group_id', $group->id)->firstOrFail();

        $payment->forceFill(['status' => PaymentStatus::Paid])->save();

        (new ProcessSuccessfulPayment($payment->fresh()))->handle();

        $wallet = Wallet::forUser($user, Currency::Try);
        $this->assertSame(350.0, $wallet->totalAvailableBalance());
        $this->assertSame(9, $user->fresh()->spinCreditBalance());
    }

    public function test_bank_transfer_payment_gets_a_reference_code_but_card_payment_does_not(): void
    {
        $user = User::factory()->create();
        $billing = BillingProfile::factory()->create(['user_id' => $user->id]);

        $bankCart = Cart::factory()->create(['user_id' => $user->id, 'status' => CartStatus::Active]);
        CartItem::factory()->create([
            'cart_id' => $bankCart->id,
            'price' => 100,
            'currency' => Currency::Try,
        ]);
        $bankGroup = app(CartCheckoutService::class)->checkout($bankCart, $billing, method: PaymentMethod::BankTransfer);
        $bankPayment = Payment::query()->where('order_group_id', $bankGroup->id)->firstOrFail();

        $this->assertNotNull($bankPayment->reference_code);
        $this->assertStringStartsWith('NT-', $bankPayment->reference_code);

        $cardCart = Cart::factory()->create(['user_id' => $user->id, 'status' => CartStatus::Active]);
        CartItem::factory()->create([
            'cart_id' => $cardCart->id,
            'price' => 100,
            'currency' => Currency::Try,
        ]);
        $cardGroup = app(CartCheckoutService::class)->checkout($cardCart, $billing, method: PaymentMethod::Card);
        $cardPayment = Payment::query()->where('order_group_id', $cardGroup->id)->firstOrFail();

        $this->assertNull($cardPayment->reference_code);
    }
}
