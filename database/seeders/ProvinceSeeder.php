<?php

namespace Database\Seeders;

use App\Models\Province;
use App\Support\TurkishProvinces;
use Illuminate\Database\Seeder;

class ProvinceSeeder extends Seeder
{
    public function run(): void
    {
        foreach (TurkishProvinces::all() as $province) {
            Province::query()->updateOrCreate(
                ['slug' => $province['slug']],
                $province,
            );
        }
    }
}
