<?php

namespace Database\Factories;

use App\Enums\Currency;
use App\Models\BillingProfile;
use App\Models\OrderGroup;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderGroup>
 */
class OrderGroupFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 100, 1000);

        return [
            'user_id' => User::factory(),
            'subtotal' => $subtotal,
            'discount_tier_amount' => 0,
            'coupon_discount_amount' => 0,
            'vat_amount' => null,
            'vat_withholding_amount' => null,
            'total' => $subtotal,
            'currency' => Currency::Try,
            'billing_profile_id' => null,
            'contract_accepted_at' => now(),
        ];
    }

    public function withBillingProfile(?BillingProfile $profile = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'billing_profile_id' => $profile?->id ?? BillingProfile::factory(),
        ]);
    }
}
