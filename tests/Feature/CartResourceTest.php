<?php

namespace Tests\Feature;

use App\Filament\Resources\Carts\Pages\ListCarts;
use App\Filament\Resources\Carts\Pages\ViewCart;
use App\Filament\Resources\OrderGroups\OrderGroupResource;
use App\Filament\Resources\Sites\Pages\ListSites;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CartResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_only_sees_carts_with_items(): void
    {
        $admin = User::factory()->admin()->create();
        $emptyCart = Cart::factory()->create();
        $filledCart = Cart::factory()->create();
        CartItem::factory()->create(['cart_id' => $filledCart->id, 'price' => 120]);

        $this->actingAs($admin);

        Livewire::test(ListCarts::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$filledCart])
            ->assertCanNotSeeTableRecords([$emptyCart]);
    }

    public function test_admin_can_view_cart_details(): void
    {
        $admin = User::factory()->admin()->create();
        $cart = Cart::factory()->create();
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'price' => 150,
            'configured_at' => now(),
        ]);

        $this->actingAs($admin);

        Livewire::test(ViewCart::class, ['record' => $cart->id])
            ->assertSuccessful()
            ->assertSee('Sepet kalemleri')
            ->assertSee('Sepet özeti')
            ->assertSee('Yapılandırıldı');
    }

    public function test_order_group_resource_is_hidden_from_panel(): void
    {
        $this->assertFalse(OrderGroupResource::shouldRegisterNavigation());
        $this->assertFalse(OrderGroupResource::canAccess());
    }

    public function test_site_metric_columns_are_hidden_by_default(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin);

        $component = Livewire::test(ListSites::class)->assertSuccessful();

        foreach ([
            'da_value',
            'pa_value',
            'spam_score_value',
            'semrush_authority_score_value',
            'ahrefs_dr_value',
            'ahrefs_keywords_value',
            'monthly_traffic_value',
            'backlinks_value',
        ] as $columnName) {
            $column = $component->instance()->getTable()->getColumn($columnName);

            $this->assertNotNull($column);
            $this->assertTrue($column->isToggleable());
            $this->assertTrue($column->isToggledHiddenByDefault());
            $this->assertTrue($column->isToggledHidden());
        }
    }
}
