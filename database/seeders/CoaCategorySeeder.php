<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\CoaCategory;
use Illuminate\Database\Seeder;

class CoaCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Salary', 'type' => 'income'],
            ['name' => 'Other Income', 'type' => 'income'],
            ['name' => 'Family Expense', 'type' => 'expense'],
            ['name' => 'Transport Expense', 'type' => 'expense'],
            ['name' => 'Meal Expense', 'type' => 'expense'],
        ];

        foreach ($categories as $category) {
            CoaCategory::updateOrCreate(
                ['name' => $category['name']],
                ['type' => $category['type']]
            );
        }
    }
}
