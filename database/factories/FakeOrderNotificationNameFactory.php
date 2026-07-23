<?php

namespace Database\Factories;

use App\Models\FakeOrderNotificationName;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FakeOrderNotificationName>
 */
class FakeOrderNotificationNameFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->firstName(),
            'city' => fake()->city(),
        ];
    }
}
