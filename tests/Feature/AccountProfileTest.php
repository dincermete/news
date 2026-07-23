<?php

namespace Tests\Feature;

use App\Enums\BillingProfileType;
use App\Models\BillingProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountProfileTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    protected function validProfilePayload(User $user, array $overrides = []): array
    {
        return array_merge([
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'billing_type' => BillingProfileType::Individual->value,
            'tax_id' => '12345678901',
            'city' => 'İstanbul',
            'district' => 'Kadıköy',
            'address' => 'Test mah. No:1',
        ], $overrides);
    }

    public function test_profile_can_be_updated_with_consents_and_billing(): void
    {
        $user = User::factory()->customer()->create([
            'email_consent' => false,
            'sms_consent' => false,
        ]);

        $this->actingAs($user)
            ->put(route('account.profile.update'), $this->validProfilePayload($user, [
                'name' => 'Yeni İsim',
                'phone' => '5551112233',
                'email_consent' => '1',
                'sms_consent' => '1',
            ]))
            ->assertRedirect(route('account.profile'));

        $user->refresh();

        $this->assertSame('Yeni İsim', $user->name);
        $this->assertTrue($user->email_consent);
        $this->assertTrue($user->sms_consent);

        $this->assertDatabaseHas(BillingProfile::class, [
            'user_id' => $user->id,
            'type' => BillingProfileType::Individual->value,
            'tax_id' => '12345678901',
            'city' => 'İstanbul',
            'district' => 'Kadıköy',
        ]);
    }

    public function test_password_changes_when_provided_without_current_password(): void
    {
        $user = User::factory()->customer()->create([
            'password' => Hash::make('old-password'),
        ]);

        $this->actingAs($user)
            ->put(route('account.profile.update'), $this->validProfilePayload($user, [
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ]))
            ->assertRedirect(route('account.profile'));

        $this->assertTrue(Hash::check('new-password-123', $user->fresh()->password));
    }

    public function test_password_unchanged_when_left_blank(): void
    {
        $user = User::factory()->customer()->create([
            'password' => Hash::make('keep-password'),
        ]);

        $this->actingAs($user)
            ->put(route('account.profile.update'), $this->validProfilePayload($user, [
                'password' => '',
                'password_confirmation' => '',
            ]))
            ->assertRedirect(route('account.profile'));

        $this->assertTrue(Hash::check('keep-password', $user->fresh()->password));
    }

    public function test_consent_toggles_can_be_turned_off(): void
    {
        $user = User::factory()->customer()->create([
            'email_consent' => true,
            'sms_consent' => true,
        ]);

        $this->actingAs($user)
            ->put(route('account.profile.update'), $this->validProfilePayload($user))
            ->assertRedirect(route('account.profile'));

        $user->refresh();

        $this->assertFalse($user->email_consent);
        $this->assertFalse($user->sms_consent);
    }
}
