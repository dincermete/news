<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\BankAccount>
 */
class BankAccountFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'short_code' => mb_strtoupper(fake()->lexify('???')),
            'account_name' => 'NewsTanıtım',
            'iban' => 'TR'.fake()->numerify(str_repeat('#', 24)),
            'sort_order' => 0,
            'is_active' => true,
        ];
    }
}
