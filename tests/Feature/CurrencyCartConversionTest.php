<?php

namespace Tests\Feature;

use App\Enums\CartStatus;
use App\Enums\Currency;
use App\Enums\ProductType;
use App\Enums\SiteStatus;
use App\Models\BillingProfile;
use App\Models\Cart;
use App\Models\ExchangeRate;
use App\Models\Site;
use App\Models\User;
use App\Services\CartCheckoutService;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CurrencyCartConversionTest extends TestCase
{
    use RefreshDatabase;

    protected function seedUsdRate(float $rate = 34.12): ExchangeRate
    {
        return ExchangeRate::query()->create([
            'base_currency' => Currency::Try,
            'quote_currency' => Currency::Usd,
            'rate' => $rate,
            'rate_date' => now()->toDateString(),
            'source' => 'tcmb',
            'fetched_at' => now(),
        ]);
    }

    public function test_usd_site_is_converted_to_try_on_add(): void
    {
        $rate = $this->seedUsdRate(34.12);
        $user = User::factory()->create();
        $cart = Cart::factory()->create(['user_id' => $user->id, 'status' => CartStatus::Active]);
        $site = Site::factory()->create([
            'price' => 100,
            'discount_price' => null,
            'currency' => Currency::Usd,
            'status' => SiteStatus::Active,
        ]);

        $item = app(CartService::class)->addSiteArticle($cart, $site);

        $this->assertSame(Currency::Try, $item->currency);
        $this->assertSame('3412.00', $item->price);
        $this->assertSame('100.00', $item->source_price);
        $this->assertSame(Currency::Usd, $item->source_currency);
        $this->assertSame('34.120000', $item->exchange_rate);
        $this->assertSame($rate->id, $item->exchange_rate_id);
    }

    public function test_summarize_reprices_when_rate_changes(): void
    {
        $this->seedUsdRate(30.0);
        $user = User::factory()->create();
        $cart = Cart::factory()->create(['user_id' => $user->id, 'status' => CartStatus::Active]);
        $site = Site::factory()->create([
            'price' => 10,
            'discount_price' => null,
            'currency' => Currency::Usd,
            'status' => SiteStatus::Active,
        ]);

        $carts = app(CartService::class);
        $item = $carts->addSiteArticle($cart, $site);
        $this->assertSame('300.00', $item->price);

        ExchangeRate::query()->create([
            'base_currency' => Currency::Try,
            'quote_currency' => Currency::Usd,
            'rate' => 40.0,
            'rate_date' => now()->addDay()->toDateString(),
            'source' => 'tcmb',
            'fetched_at' => now(),
        ]);

        $summary = $carts->summarize($cart->fresh());
        $item = $item->fresh();

        $this->assertSame(400.0, $summary['subtotal']);
        $this->assertSame('400.00', $item->price);
        $this->assertSame('10.00', $item->source_price);
        $this->assertSame('40.000000', $item->exchange_rate);
    }

    public function test_checkout_copies_exchange_rate_snapshot_to_orders(): void
    {
        $rate = $this->seedUsdRate(34.12);
        $user = User::factory()->create();
        $billing = BillingProfile::factory()->create(['user_id' => $user->id]);
        $cart = Cart::factory()->create(['user_id' => $user->id, 'status' => CartStatus::Active]);
        $site = Site::factory()->create([
            'price' => 50,
            'discount_price' => null,
            'currency' => Currency::Usd,
            'status' => SiteStatus::Active,
        ]);

        app(CartService::class)->addSiteArticle($cart, $site);

        $group = app(CartCheckoutService::class)->checkout($cart->fresh('items'), $billing);
        $order = $group->orders->first();

        $this->assertSame(Currency::Try, $group->currency);
        $this->assertSame('1706.00', $group->subtotal);
        $this->assertSame(Currency::Try, $order->currency);
        $this->assertSame('1706.00', $order->price);
        $this->assertSame('50.00', $order->source_price);
        $this->assertSame(Currency::Usd, $order->source_currency);
        $this->assertSame('34.120000', $order->exchange_rate);
        $this->assertSame($rate->id, $order->exchange_rate_id);
        $this->assertSame(ProductType::SiteArticle, $order->product_type);
    }
}
