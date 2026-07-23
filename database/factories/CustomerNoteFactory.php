<?php

namespace Database\Factories;

use App\Models\CustomerNote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomerNote>
 */
class CustomerNoteFactory extends Factory
{
    protected $model = CustomerNote::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->customer(),
            'admin_id' => User::factory()->admin(),
            'body' => fake()->paragraph(),
        ];
    }
}
