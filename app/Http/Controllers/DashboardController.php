<?php

namespace App\Http\Controllers;

use App\Models\FinancialTransaction;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        // Calculate Total Income
        // Normal balance for Income is Credit (Credit increases income, Debit decreases it)
        $totalIncomeCredit = FinancialTransaction::whereHas('coa.coaCategory', function ($query) {
            $query->where('type', 'income');
        })->sum('credit');
        
        $totalIncomeDebit = FinancialTransaction::whereHas('coa.coaCategory', function ($query) {
            $query->where('type', 'income');
        })->sum('debit');

        $totalIncome = $totalIncomeCredit - $totalIncomeDebit;

        // Calculate Total Expense
        // Normal balance for Expense is Debit (Debit increases expense, Credit decreases it)
        $totalExpenseDebit = FinancialTransaction::whereHas('coa.coaCategory', function ($query) {
            $query->where('type', 'expense');
        })->sum('debit');

        $totalExpenseCredit = FinancialTransaction::whereHas('coa.coaCategory', function ($query) {
            $query->where('type', 'expense');
        })->sum('credit');

        $totalExpense = $totalExpenseDebit - $totalExpenseCredit;

        // Calculate Net Income
        $netIncome = $totalIncome - $totalExpense;

        // Get 5 latest transactions
        $recentTransactions = FinancialTransaction::with(['coa.coaCategory'])
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->take(5)
            ->get();

        // Get Trend Data (Last 6 Months)
        $sixMonthsAgo = \Carbon\Carbon::now()->subMonths(5)->startOfMonth();
        
        $trendTransactions = FinancialTransaction::with('coa.coaCategory')
            ->where('transaction_date', '>=', $sixMonthsAgo->format('Y-m-d'))
            ->get();

        $trendDataMap = [];
        // Initialize last 6 months to ensure chronological order even if no data
        for ($i = 5; $i >= 0; $i--) {
            $date = \Carbon\Carbon::now()->subMonths($i);
            $key = $date->format('Y-m');
            $trendDataMap[$key] = [
                'label' => $date->format('M Y'),
                'income' => 0,
                'expense' => 0,
                'net' => 0,
            ];
        }

        foreach ($trendTransactions as $tx) {
            $monthYear = \Carbon\Carbon::parse($tx->transaction_date)->format('Y-m');
            
            if (isset($trendDataMap[$monthYear])) {
                $type = $tx->coa->coaCategory->type ?? 'unknown';
                if ($type === 'income') {
                    $trendDataMap[$monthYear]['income'] += ($tx->credit - $tx->debit);
                } elseif ($type === 'expense') {
                    $trendDataMap[$monthYear]['expense'] += ($tx->debit - $tx->credit);
                }
            }
        }

        foreach ($trendDataMap as $key => $data) {
            $trendDataMap[$key]['net'] = $data['income'] - $data['expense'];
        }

        $trendData = array_values($trendDataMap);

        // Sankey Diagram Data
        $sankeyPeriod = request('sankey_period', '1_month');
        $sankeyQuery = FinancialTransaction::with('coa.coaCategory');

        if ($sankeyPeriod === '1_month') {
            $sankeyQuery->where('transaction_date', '>=', \Carbon\Carbon::now()->subMonth()->format('Y-m-d'));
        } elseif ($sankeyPeriod === '3_months') {
            $sankeyQuery->where('transaction_date', '>=', \Carbon\Carbon::now()->subMonths(3)->format('Y-m-d'));
        } elseif ($sankeyPeriod === '1_year') {
            $sankeyQuery->where('transaction_date', '>=', \Carbon\Carbon::now()->subYear()->format('Y-m-d'));
        } elseif ($sankeyPeriod === '5_years') {
            $sankeyQuery->where('transaction_date', '>=', \Carbon\Carbon::now()->subYears(5)->format('Y-m-d'));
        } elseif ($sankeyPeriod === 'all_time') {
            // No filter
        }

        $sankeyTransactions = $sankeyQuery->get();

        $sankeyNodes = [];
        $sankeyLinks = [];
        
        $incomeCategories = [];
        $expenseCategories = [];

        $totalSankeyIncome = 0;
        $totalSankeyExpense = 0;

        foreach ($sankeyTransactions as $tx) {
            $catName = $tx->coa->coaCategory->name ?? 'Unknown';
            $type = $tx->coa->coaCategory->type ?? 'unknown';

            if ($type === 'income') {
                if (!isset($incomeCategories[$catName])) $incomeCategories[$catName] = 0;
                $incomeCategories[$catName] += ($tx->credit - $tx->debit);
            } elseif ($type === 'expense') {
                if (!isset($expenseCategories[$catName])) $expenseCategories[$catName] = 0;
                $expenseCategories[$catName] += ($tx->debit - $tx->credit);
            }
        }

        foreach ($incomeCategories as $name => $amount) {
            if ($amount > 0) {
                $sankeyNodes[] = ['name' => $name];
                $sankeyLinks[] = ['source' => $name, 'target' => 'Total Income', 'value' => $amount];
                $totalSankeyIncome += $amount;
            }
        }

        foreach ($expenseCategories as $name => $amount) {
            if ($amount > 0) {
                $sankeyNodes[] = ['name' => $name];
                $sankeyLinks[] = ['source' => 'Total Expense', 'target' => $name, 'value' => $amount];
                $totalSankeyExpense += $amount;
            }
        }

        $net = $totalSankeyIncome - $totalSankeyExpense;

        if ($totalSankeyIncome > 0 || $totalSankeyExpense > 0) {
            $sankeyNodes[] = ['name' => 'Total Income'];
            $sankeyNodes[] = ['name' => 'Total Expense'];

            if ($net >= 0) {
                $sankeyNodes[] = ['name' => 'Net Income'];
                if ($totalSankeyExpense > 0) {
                    $sankeyLinks[] = ['source' => 'Total Income', 'target' => 'Total Expense', 'value' => $totalSankeyExpense];
                }
                if ($net > 0) {
                    $sankeyLinks[] = ['source' => 'Total Income', 'target' => 'Net Income', 'value' => $net];
                }
            } else {
                $deficit = abs($net);
                $sankeyNodes[] = ['name' => 'Deficit (Kerugian)'];
                
                if ($totalSankeyIncome > 0) {
                    $sankeyLinks[] = ['source' => 'Total Income', 'target' => 'Total Expense', 'value' => $totalSankeyIncome];
                }
                $sankeyLinks[] = ['source' => 'Deficit (Kerugian)', 'target' => 'Total Expense', 'value' => $deficit];
            }
        }

        $sankeyNodes = array_values(array_map("unserialize", array_unique(array_map("serialize", $sankeyNodes))));

        return Inertia::render('Dashboard', [
            'totalIncome' => (float)$totalIncome,
            'totalExpense' => (float)$totalExpense,
            'netIncome' => (float)$netIncome,
            'recentTransactions' => $recentTransactions,
            'trendData' => $trendData,
            'sankeyData' => [
                'nodes' => $sankeyNodes,
                'links' => $sankeyLinks,
                'period' => $sankeyPeriod,
            ],
        ]);
    }
}
