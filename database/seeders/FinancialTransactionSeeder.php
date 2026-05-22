<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FinancialTransaction;
use App\Models\Coa;
use Carbon\Carbon;

class FinancialTransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Bersihkan data transaksi yang ada agar tidak menumpuk berkali-kali
        FinancialTransaction::truncate();

        // Pastikan ada COA terlebih dahulu
        $coas = Coa::with('coaCategory')->get();

        if ($coas->isEmpty()) {
            return;
        }

        // Pisahkan COA berdasarkan tipe
        $incomeCoas = $coas->filter(fn($coa) => $coa->coaCategory && $coa->coaCategory->type === 'income');
        $expenseCoas = $coas->filter(fn($coa) => $coa->coaCategory && $coa->coaCategory->type === 'expense');

        $transactions = [];

        // Buat data untuk bulan ini dan bulan lalu
        $monthsToSeed = [
            Carbon::now(),
            Carbon::now()->subMonth(),
        ];

        foreach ($monthsToSeed as $date) {
            // Seed beberapa transaksi income (normalnya Credit > 0)
            if ($incomeCoas->isNotEmpty()) {
                for ($i = 0; $i < rand(2, 4); $i++) {
                    $tDate = $date->copy()->startOfMonth()->addDays(rand(0, 27));
                    $transactions[] = [
                        'transaction_date' => $tDate->format('Y-m-d'),
                        'coa_id' => $incomeCoas->random()->id,
                        'description' => 'Pendapatan Acak ' . $date->format('M Y') . ' #' . ($i + 1),
                        'debit' => 0,
                        'credit' => rand(150, 800) * 10000, // Range 1.5jt - 8jt
                        'created_at' => $tDate->copy()->addHours(rand(8, 17))->addMinutes(rand(0, 59)),
                        'updated_at' => $tDate->copy()->addHours(rand(8, 17))->addMinutes(rand(0, 59)),
                    ];
                }
            }

            // Seed beberapa transaksi expense (normalnya Debit > 0)
            if ($expenseCoas->isNotEmpty()) {
                for ($i = 0; $i < rand(8, 15); $i++) {
                    $tDate = $date->copy()->startOfMonth()->addDays(rand(0, 27));
                    $transactions[] = [
                        'transaction_date' => $tDate->format('Y-m-d'),
                        'coa_id' => $expenseCoas->random()->id,
                        'description' => 'Pengeluaran Acak ' . $date->format('M Y') . ' #' . ($i + 1),
                        'debit' => rand(10, 100) * 10000, // Range 100k - 1jt
                        'credit' => 0,
                        'created_at' => $tDate->copy()->addHours(rand(8, 17))->addMinutes(rand(0, 59)),
                        'updated_at' => $tDate->copy()->addHours(rand(8, 17))->addMinutes(rand(0, 59)),
                    ];
                }
            }
        }

        FinancialTransaction::insert($transactions);
    }
}
