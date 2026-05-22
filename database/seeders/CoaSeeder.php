<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Coa;
use App\Models\CoaCategory;

class CoaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Fetch categories dynamically to get their IDs
        $salaryCategory = CoaCategory::where('name', 'Salary')->first();
        $otherIncomeCategory = CoaCategory::where('name', 'Other Income')->first();
        $familyExpenseCategory = CoaCategory::where('name', 'Family Expense')->first();
        $transportExpenseCategory = CoaCategory::where('name', 'Transport Expense')->first();
        $mealExpenseCategory = CoaCategory::where('name', 'Meal Expense')->first();

        $coas = [
            // Salary
            [
                'code' => '101',
                'name' => 'Gaji Pokok',
                'coa_category_id' => $salaryCategory?->id,
            ],
            // Other Income
            [
                'code' => '102',
                'name' => 'Pendapatan Sampingan',
                'coa_category_id' => $otherIncomeCategory?->id,
            ],
            // Family/Business Expenses
            [
                'code' => '401',
                'name' => 'Gaji Karyawan',
                'coa_category_id' => $familyExpenseCategory?->id,
            ],
            [
                'code' => '501',
                'name' => 'Belanja Bulanan',
                'coa_category_id' => $familyExpenseCategory?->id,
            ],
            // Transport Expense
            [
                'code' => '502',
                'name' => 'Bensin & Tol',
                'coa_category_id' => $transportExpenseCategory?->id,
            ],
            // Meal Expense
            [
                'code' => '503',
                'name' => 'Makanan & Restoran',
                'coa_category_id' => $mealExpenseCategory?->id,
            ],
        ];

        foreach ($coas as $coa) {
            if ($coa['coa_category_id']) {
                Coa::updateOrCreate(
                    ['code' => $coa['code']],
                    [
                        'name' => $coa['name'],
                        'coa_category_id' => $coa['coa_category_id']
                    ]
                );
            }
        }
    }
}
