<?php

namespace Database\Factories;

use App\Models\Province;
use App\Support\TurkishProvinces;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Province>
 */
class ProvinceFactory extends Factory
{
    protected $model = Province::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $provinces = TurkishProvinces::all();
        $pick = fake()->unique()->randomElement($provinces);

        return [
            'name' => $pick['name'],
            'slug' => $pick['slug'],
            'plate_code' => $pick['plate_code'],
            'name_locative' => $pick['name_locative'],
        ];
    }

    public function nevsehir(): static
    {
        return $this->state(fn (): array => [
            'name' => 'Nevşehir',
            'slug' => 'nevsehir',
            'plate_code' => '50',
            'name_locative' => "Nevşehir'de",
        ]);
    }

    public function istanbul(): static
    {
        return $this->state(fn (): array => [
            'name' => 'İstanbul',
            'slug' => 'istanbul',
            'plate_code' => '34',
            'name_locative' => "İstanbul'da",
        ]);
    }
}
