<?php

namespace Tests\Feature;

use App\Enums\CouponType;
use App\Filament\Resources\Coupons\Pages\CreateCoupon;
use App\Filament\Resources\Coupons\Pages\ListCoupons;
use App\Models\Coupon;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CouponResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_coupons(): void
    {
        $admin = User::factory()->admin()->create();
        $coupons = Coupon::factory()->count(2)->create();

        $this->actingAs($admin);

        Livewire::test(ListCoupons::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords($coupons);
    }

    public function test_admin_can_create_coupon(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin);

        Livewire::test(CreateCoupon::class)
            ->fillForm([
                'code' => 'SAVE10',
                'type' => CouponType::Percentage->value,
                'value' => 10,
                'usage_limit' => 50,
                'used_count' => 0,
                'is_active' => true,
                'valid_from' => now()->subDay(),
                'valid_until' => now()->addMonth(),
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertNotified()
            ->assertRedirect();

        $this->assertDatabaseHas(Coupon::class, [
            'code' => 'SAVE10',
            'type' => CouponType::Percentage->value,
            'value' => 10,
        ]);
    }
}
