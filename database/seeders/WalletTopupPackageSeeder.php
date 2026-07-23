<?php

namespace Database\Seeders;

use App\Models\WalletTopupPackage;
use Illuminate\Database\Seeder;

class WalletTopupPackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            ['amount' => 50, 'spin_credits' => 1, 'sort_order' => 1],
            ['amount' => 100, 'spin_credits' => 3, 'sort_order' => 2],
            ['amount' => 200, 'spin_credits' => 6, 'sort_order' => 3],
            ['amount' => 500, 'spin_credits' => 15, 'sort_order' => 4],
            ['amount' => 1000, 'spin_credits' => 35, 'sort_order' => 5],
            ['amount' => 1500, 'spin_credits' => 50, 'sort_order' => 6],
            ['amount' => 5000, 'spin_credits' => 175, 'sort_order' => 7],
            ['amount' => 10000, 'spin_credits' => 350, 'sort_order' => 8],
            ['amount' => 50000, 'spin_credits' => 1900, 'sort_order' => 9],
        ];

        foreach ($packages as $package) {
            WalletTopupPackage::query()->updateOrCreate(
                ['amount' => $package['amount']],
                [
                    'spin_credits' => $package['spin_credits'],
                    'sort_order' => $package['sort_order'],
                    'is_active' => true,
                ],
            );
        }
    }
}
