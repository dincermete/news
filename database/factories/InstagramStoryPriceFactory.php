<?php

namespace Database\Factories;

use App\Enums\StoryFormat;
use App\Models\InstagramAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\InstagramStoryPrice>
 */
class InstagramStoryPriceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'instagram_account_id' => InstagramAccount::factory(),
            'format' => fake()->randomElement(StoryFormat::cases()),
            'price' => fake()->randomFloat(2, 1000, 8000),
            'currency' => 'TRY',
            'is_active' => true,
        ];
    }
}
