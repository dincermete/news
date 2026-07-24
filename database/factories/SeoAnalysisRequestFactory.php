<?php

namespace Database\Factories;

use App\Enums\SeoAnalysisServiceType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\SeoAnalysisRequest>
 */
class SeoAnalysisRequestFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'site_url' => fake()->url(),
            'service_type' => fake()->randomElement(SeoAnalysisServiceType::cases()),
            'brief' => fake()->optional()->sentence(),
        ];
    }
}
