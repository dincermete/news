<?php

namespace Database\Seeders;

use App\Enums\CustomerStatus;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            WalletTopupPackageSeeder::class,
            BankAccountSeeder::class,
            ArticleWordPackageSeeder::class,
            PageSeeder::class,
            DemoCatalogSeeder::class,
        ]);

        User::query()->updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'role' => UserRole::Admin,
                'status' => CustomerStatus::Active,
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ],
        );
    }
}
