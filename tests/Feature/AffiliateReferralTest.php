<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AffiliateReferralTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_with_valid_ref_sets_referred_by_id(): void
    {
        $referrer = User::factory()->customer()->create([
            'affiliate_code' => 'ABCD1234',
        ]);

        $response = $this->post(route('register.store'), [
            'name' => 'Yeni Üye',
            'email' => 'yeni@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'ref' => 'ABCD1234',
        ]);

        $response->assertRedirect(route('account.dashboard'));

        $user = User::query()->where('email', 'yeni@example.com')->first();

        $this->assertNotNull($user);
        $this->assertSame(UserRole::Customer, $user->role);
        $this->assertSame($referrer->id, $user->referred_by_id);
    }

    public function test_registration_with_invalid_ref_is_silently_ignored(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Yeni Üye',
            'email' => 'yeni2@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'ref' => 'NOTEXIST',
        ]);

        $response->assertRedirect(route('account.dashboard'));

        $user = User::query()->where('email', 'yeni2@example.com')->first();

        $this->assertNotNull($user);
        $this->assertNull($user->referred_by_id);
    }

    public function test_register_form_accepts_ref_query_parameter(): void
    {
        $this->get(route('register', ['ref' => 'ABCD1234']))
            ->assertOk()
            ->assertSee('name="ref"', false)
            ->assertSee('value="ABCD1234"', false);
    }

    public function test_affiliate_page_generates_code_and_renders(): void
    {
        $user = User::factory()->customer()->create([
            'affiliate_code' => null,
            'affiliate_commission_rate' => null,
        ]);

        $this->actingAs($user)
            ->get(route('account.affiliate'))
            ->assertOk()
            ->assertSee('Komisyon oranınız henüz tanımlanmamış')
            ->assertSee('/kayitol?ref=');

        $this->assertNotNull($user->fresh()->affiliate_code);
        $this->assertSame(8, strlen((string) $user->fresh()->affiliate_code));
    }
}
