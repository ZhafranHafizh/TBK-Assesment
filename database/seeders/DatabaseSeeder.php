<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed default user tanpa duplikasi
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Test User', 'password' => bcrypt('password')]
        );

        // Seed chart of accounts categories, master COA, dan Transaksi Dummy
        $this->call([
            AppSettingSeeder::class,
            CoaCategorySeeder::class,
            CoaSeeder::class,
            FinancialTransactionSeeder::class,
        ]);
    }
}
