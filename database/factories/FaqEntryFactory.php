<?php

namespace Database\Factories;

use App\Models\FaqEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FaqEntry>
 */
class FaqEntryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'question_topic' => fake()->sentence(3),
            'answer' => fake()->paragraph(),
            'category' => fake()->randomElement(['genel', 'seo', 'odeme', 'surec']),
            'is_active' => true,
        ];
    }
}
