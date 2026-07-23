<?php

namespace Database\Factories;

use App\Enums\BillingProfileType;
use App\Models\BillingProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BillingProfile>
 */
class BillingProfileFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(BillingProfileType::cases());

        return [
            'user_id' => User::factory(),
            'type' => $type,
            'tax_id' => $type === BillingProfileType::Corporate
                ? fake()->numerify('##########')
                : fake()->numerify('###########'),
            'company_name' => $type === BillingProfileType::Corporate ? fake()->company() : null,
            'address' => fake()->address(),
            'tax_office' => $type === BillingProfileType::Corporate ? fake()->city().' Vergi Dairesi' : null,
        ];
    }

    public function corporate(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => BillingProfileType::Corporate,
            'company_name' => fake()->company(),
            'tax_office' => fake()->city().' Vergi Dairesi',
        ]);
    }

    public function individual(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => BillingProfileType::Individual,
            'company_name' => null,
            'tax_office' => null,
        ]);
    }
}
