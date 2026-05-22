<?php

namespace App\Services;

use App\Models\CoaCategory;
use App\Models\FinancialTransaction;

class ReportService
{
    public function getProfitLoss(int $month, int $year): array
    {
        // 1. Ambil kategori master
        $incomeCategories = CoaCategory::where('type', 'income')->get();
        $expenseCategories = CoaCategory::where('type', 'expense')->get();

        $incomes = [];
        $totalIncome = 0;

        // 2. Hitung tiap kategori income (Credit - Debit)
        foreach ($incomeCategories as $cat) {
            $sum = FinancialTransaction::whereHas('coa', function ($q) use ($cat) {
                $q->where('coa_category_id', $cat->id);
            })
            ->whereMonth('transaction_date', $month)
            ->whereYear('transaction_date', $year)
            ->selectRaw('SUM(credit) - SUM(debit) as total')
            ->value('total') ?? 0;

            $incomes[] = [
                'name' => $cat->name,
                'total' => (float) $sum
            ];
            $totalIncome += $sum;
        }

        $expenses = [];
        $totalExpense = 0;

        // 3. Hitung tiap kategori expense (Debit - Credit)
        foreach ($expenseCategories as $cat) {
            $sum = FinancialTransaction::whereHas('coa', function ($q) use ($cat) {
                $q->where('coa_category_id', $cat->id);
            })
            ->whereMonth('transaction_date', $month)
            ->whereYear('transaction_date', $year)
            ->selectRaw('SUM(debit) - SUM(credit) as total')
            ->value('total') ?? 0;

            $expenses[] = [
                'name' => $cat->name,
                'total' => (float) $sum
            ];
            $totalExpense += $sum;
        }

        // 4. Return array terstruktur
        return [
            'period' => [
                'month' => $month,
                'year' => $year,
            ],
            'incomes' => $incomes,
            'total_income' => $totalIncome,
            'expenses' => $expenses,
            'total_expense' => $totalExpense,
            'net_income' => $totalIncome - $totalExpense
        ];
    }
}
