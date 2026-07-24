<?php

namespace Tests\Feature;

use App\Enums\CartStatus;
use App\Enums\Currency;
use App\Enums\ProductType;
use App\Models\BillingProfile;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\DiscountTier;
use App\Models\Order;
use App\Models\OrderGroup;
use App\Models\Site;
use App\Models\SiteBundle;
use App\Models\User;
use App\Services\CartCheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartCheckoutServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_creates_orders_and_order_group_from_cart_items(): void
    {
        $user = User::factory()->create();
        $billing = BillingProfile::factory()->create(['user_id' => $user->id]);
        $cart = Cart::factory()->create(['user_id' => $user->id, 'status' => CartStatus::Active]);

        $site = Site::factory()->create(['price' => 100]);
        $bundle = SiteBundle::factory()->create(['price' => 200]);

        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_type' => ProductType::SiteArticle,
            'site_id' => $site->id,
            'price' => 100,
            'currency' => Currency::Try,
        ]);
        CartItem::factory()->bundle()->create([
            'cart_id' => $cart->id,
            'site_bundle_id' => $bundle->id,
            'price' => 200,
            'currency' => Currency::Try,
        ]);

        $group = app(CartCheckoutService::class)->checkout($cart, $billing);

        $this->assertInstanceOf(OrderGroup::class, $group);
        $this->assertCount(2, $group->orders);
        $this->assertSame('300.00', $group->subtotal);
        $this->assertSame('300.00', $group->total);
        $this->assertSame(CartStatus::Converted, $cart->fresh()->status);
        $this->assertSame(2, Order::query()->where('order_group_id', $group->id)->count());
    }

    public function test_checkout_succeeds_without_billing_profile(): void
    {
        $user = User::factory()->create();
        $cart = Cart::factory()->create(['user_id' => $user->id, 'status' => CartStatus::Active]);

        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_type' => ProductType::SiteArticle,
            'site_id' => Site::factory()->create(['price' => 100])->id,
            'price' => 100,
            'currency' => Currency::Try,
        ]);

        $group = app(CartCheckoutService::class)->checkout($cart, null);

        $this->assertInstanceOf(OrderGroup::class, $group);
        $this->assertSame($user->id, $group->user_id);
        $this->assertNull($group->billing_profile_id);
        $this->assertCount(1, $group->orders);
    }

    public function test_checkout_applies_highest_matching_discount_tier(): void
    {
        $user = User::factory()->create();
        $billing = BillingProfile::factory()->create(['user_id' => $user->id]);
        $cart = Cart::factory()->create(['user_id' => $user->id]);

        DiscountTier::factory()->create([
            'min_cart_amount' => 200,
            'discount_percentage' => 5,
        ]);
        DiscountTier::factory()->create([
            'min_cart_amount' => 500,
            'discount_percentage' => 10,
        ]);

        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'price' => 600,
            'currency' => Currency::Try,
            'site_id' => Site::factory(),
        ]);

        $group = app(CartCheckoutService::class)->checkout($cart, $billing);

        // Highest matching tier is 500 → 10% = 60
        $this->assertSame('600.00', $group->subtotal);
        $this->assertSame('60.00', $group->discount_tier_amount);
        $this->assertSame('540.00', $group->total);
    }

    public function test_checkout_applies_tier_and_coupon_together(): void
    {
        $user = User::factory()->create();
        $billing = BillingProfile::factory()->create(['user_id' => $user->id]);
        $cart = Cart::factory()->create(['user_id' => $user->id]);

        DiscountTier::factory()->create([
            'min_cart_amount' => 100,
            'discount_percentage' => 10,
        ]);

        Coupon::factory()->percentage(5)->create([
            'code' => 'EXTRA5',
            'min_cart_amount' => 100,
        ]);

        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'price' => 200,
            'currency' => Currency::Try,
            'site_id' => Site::factory(),
        ]);

        $group = app(CartCheckoutService::class)->checkout($cart, $billing, 'EXTRA5');

        // Tier 10% of 200 = 20, coupon 5% of 200 = 10, total = 170
        $this->assertSame('200.00', $group->subtotal);
        $this->assertSame('20.00', $group->discount_tier_amount);
        $this->assertSame('10.00', $group->coupon_discount_amount);
        $this->assertSame('170.00', $group->total);

        $this->assertDatabaseHas(CouponRedemption::class, [
            'order_group_id' => $group->id,
            'user_id' => $user->id,
            'discount_amount' => 10,
        ]);

        $this->assertSame(1, Coupon::query()->where('code', 'EXTRA5')->value('used_count'));
    }
}
