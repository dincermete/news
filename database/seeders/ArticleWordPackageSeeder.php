<?php

namespace Database\Seeders;

use App\Models\ArticleWordPackage;
use Illuminate\Database\Seeder;

class ArticleWordPackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            100 => 15,
            200 => 30,
            300 => 45,
            400 => 60,
            500 => 50,
            600 => 59,
            700 => 70,
            800 => 80,
            900 => 90,
            1000 => 110,
        ];

        $sort = 1;

        foreach ($packages as $wordCount => $price) {
            ArticleWordPackage::query()->updateOrCreate(
                ['word_count' => $wordCount],
                [
                    'price' => $price,
                    'sort_order' => $sort++,
                    'is_active' => true,
                ],
            );
        }
    }
}
