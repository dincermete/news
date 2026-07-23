<?php

namespace Database\Factories;

use App\Models\SpinWheelPrize;
use App\Models\SpinWheelSpin;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SpinWheelSpin>
 */
class SpinWheelSpinFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'spin_wheel_prize_id' => SpinWheelPrize::factory(),
        ];
    }
}
