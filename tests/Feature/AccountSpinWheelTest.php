<?php

namespace Tests\Feature;

use App\Enums\SpinPrizeType;
use App\Http\Controllers\Account\AccountSpinWheelController;
use App\Models\SpinCreditTransaction;
use App\Models\SpinWheelPrize;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountSpinWheelTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_spin_wheel(): void
    {
        $this->get(route('account.spin-wheel'))
            ->assertRedirect(route('login'));
    }

    public function test_user_without_credits_cannot_spin(): void
    {
        $user = User::factory()->customer()->create();
        SpinWheelPrize::factory()->none()->create(['probability_weight' => 1]);

        $this->actingAs($user)
            ->postJson(route('account.spin-wheel.spin'))
            ->assertStatus(422)
            ->assertJson([
                'message' => 'Yetersiz çark kredisi.',
            ]);

        $this->assertSame(0, $user->fresh()->spinCreditBalance());
    }

    public function test_user_with_credits_can_spin_and_credit_decreases(): void
    {
        $user = User::factory()->customer()->create();
        SpinCreditTransaction::factory()->credit(3)->create(['user_id' => $user->id]);
        SpinWheelPrize::factory()->none()->create([
            'name' => 'Boş',
            'probability_weight' => 1,
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('account.spin-wheel.spin'))
            ->assertOk()
            ->assertJsonStructure([
                'segment_index',
                'spin_credits',
                'prize' => ['id', 'name', 'type', 'value', 'label'],
                'prizes',
            ]);

        $this->assertSame(2, $user->fresh()->spinCreditBalance());
        $this->assertSame(2, $response->json('spin_credits'));
    }

    public function test_segment_index_matches_ordered_prize_list(): void
    {
        $user = User::factory()->customer()->create();
        SpinCreditTransaction::factory()->credit(1)->create(['user_id' => $user->id]);

        $first = SpinWheelPrize::factory()->none()->create([
            'name' => 'Birinci',
            'probability_weight' => 0,
            'is_active' => true,
        ]);
        $second = SpinWheelPrize::factory()->balance(50)->create([
            'name' => 'İkinci',
            'probability_weight' => 100,
            'is_active' => true,
        ]);
        $third = SpinWheelPrize::factory()->none()->create([
            'name' => 'Üçüncü',
            'probability_weight' => 0,
            'is_active' => true,
        ]);

        // weight 0 prizes still appear on the wheel if available(); pickPrize skips 0 weight.
        // Ensure only second can be picked: give first/third weight 0 and second all weight.
        $first->update(['probability_weight' => 0]);
        $third->update(['probability_weight' => 0]);

        // pickPrize throws if totalWeight <= 0 when all are 0 — second has 100.
        $ordered = AccountSpinWheelController::orderedPrizes();
        $expectedIndex = $ordered->search(fn ($prize) => $prize->id === $second->id);

        $response = $this->actingAs($user)
            ->postJson(route('account.spin-wheel.spin'))
            ->assertOk();

        $this->assertSame($second->id, $response->json('prize.id'));
        $this->assertSame($expectedIndex, $response->json('segment_index'));
        $this->assertSame(
            $ordered->pluck('id')->all(),
            collect($response->json('prizes'))->pluck('id')->all(),
        );
        $this->assertSame(SpinPrizeType::Balance->value, $response->json('prize.type'));
    }

    public function test_spin_wheel_page_renders_for_authenticated_user(): void
    {
        $user = User::factory()->customer()->create();
        SpinWheelPrize::factory()->balance(10)->create();
        \App\Models\WalletTopupPackage::factory()->create([
            'amount' => 100,
            'spin_credits' => 5,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('account.spin-wheel'))
            ->assertOk()
            ->assertSee('Çarkıfelek')
            ->assertSee('Bakiye Yükledikçe Hak Kazan')
            ->assertSee('Nasıl Çalışır?');
    }
}
