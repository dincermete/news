<?php

namespace Database\Factories;

use App\Models\FakeOrderNotificationTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FakeOrderNotificationTemplate>
 */
class FakeOrderNotificationTemplateFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'message_template' => '{isim}, {sehir} şehrinden {urun} satın aldı',
            'is_active' => true,
            'display_interval_seconds' => fake()->numberBetween(15, 60),
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }
}
