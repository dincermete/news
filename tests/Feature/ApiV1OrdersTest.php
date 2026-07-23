<?php

namespace Tests\Feature;

use App\Enums\ApiTokenAbility;
use App\Enums\SiteStatus;
use App\Models\BillingProfile;
use App\Models\Order;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ApiV1OrdersTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_order_without_ability_returns_403(): void
    {
        $user = User::factory()->customer()->create();
        $token = $user->createToken('no-create', [ApiTokenAbility::ReadCatalog->value])->plainTextToken;
        $site = Site::factory()->create(['status' => SiteStatus::Active, 'price' => 100]);
        $billing = BillingProfile::factory()->create(['user_id' => $user->id]);

        $this->withToken($token)
            ->postJson('/api/v1/orders', [
                'site_id' => $site->id,
                'billing_profile_id' => $billing->id,
            ])
            ->assertForbidden();

        $this->assertSame(0, Order::query()->count());
    }

    public function test_create_order_with_ability_returns_201_and_persists_order(): void
    {
        $user = User::factory()->customer()->create();
        $token = $user->createToken('orders', [ApiTokenAbility::CreateOrder->value])->plainTextToken;
        $site = Site::factory()->create([
            'status' => SiteStatus::Active,
            'price' => 150,
            'discount_price' => null,
        ]);
        $billing = BillingProfile::factory()->create(['user_id' => $user->id]);

        $response = $this->withToken($token)
            ->postJson('/api/v1/orders', [
                'site_id' => $site->id,
                'billing_profile_id' => $billing->id,
            ])
            ->assertCreated();

        $this->assertSame(1, Order::query()->count());
        $order = Order::query()->first();
        $this->assertSame($user->id, $order->user_id);
        $this->assertSame($site->id, $order->site_id);
        $response->assertJsonPath('data.id', $order->id);
    }

    public function test_show_order_forbidden_for_other_user(): void
    {
        $owner = User::factory()->customer()->create();
        $other = User::factory()->customer()->create();
        $order = Order::factory()->create(['user_id' => $owner->id]);

        $token = $other->createToken('orders', [ApiTokenAbility::ReadOrders->value])->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/orders/'.$order->id)
            ->assertForbidden();
    }

    public function test_show_order_ok_for_owner_with_ability(): void
    {
        $owner = User::factory()->customer()->create();
        $order = Order::factory()->create(['user_id' => $owner->id]);
        $token = $owner->createToken('orders', [ApiTokenAbility::ReadOrders->value])->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/orders/'.$order->id)
            ->assertOk()
            ->assertJsonPath('data.id', $order->id);
    }

    public function test_show_order_requires_read_orders_ability(): void
    {
        $owner = User::factory()->customer()->create();
        $order = Order::factory()->create(['user_id' => $owner->id]);
        $token = $owner->createToken('orders', [ApiTokenAbility::ReadCatalog->value])->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/orders/'.$order->id)
            ->assertForbidden();
    }
}
