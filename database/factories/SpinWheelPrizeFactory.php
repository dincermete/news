<?php

namespace Database\Factories;

use App\Enums\SpinPrizeType;
use App\Models\SpinWheelPrize;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SpinWheelPrize>
 */
class SpinWheelPrizeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(SpinPrizeType::cases());

        return [
            'name' => fake()->words(2, true),
            'type' => $type,
            'value' => $type === SpinPrizeType::Balance ? fake()->randomFloat(2, 5, 100) : null,
            'probability_weight' => fake()->numberBetween(1, 10),
            'stock' => null,
            'is_active' => true,
        ];
    }

    public function balance(float $value = 10.0): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => SpinPrizeType::Balance,
            'value' => $value,
        ]);
    }

    public function none(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => SpinPrizeType::None,
            'value' => null,
        ]);
    }

    public function withStock(int $stock): static
    {
        return $this->state(fn (array $attributes): array => [
            'stock' => $stock,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }
}
