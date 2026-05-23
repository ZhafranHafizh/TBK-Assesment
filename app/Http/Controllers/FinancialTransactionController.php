<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFinancialTransactionRequest;
use App\Http\Requests\UpdateFinancialTransactionRequest;
use App\Models\FinancialTransaction;
use App\Models\Coa;
use App\Models\AllowedCurrency;
use App\Models\ExchangeRate;
use App\Services\ExchangeRateService;
use Inertia\Inertia;

class FinancialTransactionController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $coaId = $request->input('coa_id');
        $categoryId = $request->input('category_id');
        $sortBy = $request->input('sort_by', 'date_desc'); // default sort

        $query = FinancialTransaction::with('coa.coaCategory');

        // Filter: Search (Description, COA Name, COA Code)
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhereHas('coa', function($coaQuery) use ($search) {
                      $coaQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('code', 'like', "%{$search}%");
                  });
            });
        }

        // Filter: Date Range
        if ($startDate) {
            $query->where('transaction_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('transaction_date', '<=', $endDate);
        }

        // Filter: COA ID
        if ($coaId) {
            $query->where('coa_id', $coaId);
        }

        // Filter: Category ID
        if ($categoryId) {
            $query->whereHas('coa', function($q) use ($categoryId) {
                $q->where('coa_category_id', $categoryId);
            });
        }

        // Sorting Logic
        switch ($sortBy) {
            case 'date_asc':
                $query->orderBy('transaction_date', 'asc')->orderBy('id', 'asc');
                break;
            case 'expense_highest':
                $query->orderBy('debit', 'desc')->orderBy('transaction_date', 'desc');
                break;
            case 'expense_lowest':
                $query->where('debit', '>', 0)->orderBy('debit', 'asc')->orderBy('transaction_date', 'desc');
                break;
            case 'income_highest':
                $query->orderBy('credit', 'desc')->orderBy('transaction_date', 'desc');
                break;
            case 'income_lowest':
                $query->where('credit', '>', 0)->orderBy('credit', 'asc')->orderBy('transaction_date', 'desc');
                break;
            case 'date_desc':
            default:
                $query->orderByDesc('transaction_date')->orderByDesc('id');
                break;
        }

        $transactions = $query->paginate($perPage)->withQueryString();
            
        $coas = Coa::with('coaCategory')->get();
        $categories = \App\Models\CoaCategory::all();

        // Currency data for the create form
        $allowedCurrencies = AllowedCurrency::all();
        $exchangeRates = ExchangeRate::selectRaw('currency_code, rate_to_idr, fetched_at')
            ->whereIn('id', function ($q) {
                $q->selectRaw('MAX(id)')
                  ->from('exchange_rates')
                  ->groupBy('currency_code');
            })
            ->get()
            ->keyBy('currency_code');

        $rateService = app(ExchangeRateService::class);
        $isRateStale = $rateService->isRateStale();
            
        return Inertia::render('Transaction/Index', [
            'transactions' => $transactions,
            'coas' => $coas,
            'categories' => $categories,
            'allowedCurrencies' => $allowedCurrencies,
            'exchangeRates' => $exchangeRates,
            'isRateStale' => $isRateStale,
            'filters' => $request->only(['search', 'start_date', 'end_date', 'coa_id', 'category_id', 'sort_by', 'per_page'])
        ]);
    }

    public function create()
    {
        $coas = Coa::with('coaCategory')->get();
        return Inertia::render('Transaction/Create', [
            'coas' => $coas
        ]);
    }

    public function store(StoreFinancialTransactionRequest $request)
    {
        $data = $request->validated();

        // If original_currency is not IDR and not null, store metadata
        if (!empty($data['original_currency']) && $data['original_currency'] !== 'IDR') {
            // debit/credit sudah dihitung di frontend (original_amount * exchange_rate)
            // kita tetap simpan metadata-nya
            $data['original_currency'] = strtoupper($data['original_currency']);
        } else {
            // IDR transaction — null out currency metadata
            $data['original_currency'] = 'IDR';
            $data['original_amount'] = null;
            $data['exchange_rate'] = null;
        }

        FinancialTransaction::create($data);

        return redirect()->route('transactions.index')
            ->with('success', 'Transaksi berhasil dicatat.');
    }

    public function edit(FinancialTransaction $transaction)
    {
        $coas = Coa::with('coaCategory')->get();
        return Inertia::render('Transaction/Edit', [
            'transaction' => $transaction,
            'coas' => $coas
        ]);
    }

    public function update(UpdateFinancialTransactionRequest $request, FinancialTransaction $transaction)
    {
        if (!$transaction->is_editable_full) {
            $transaction->update($request->only('description'));
            
            return redirect()->route('transactions.index')
                ->with('success', 'Hanya catatan/deskripsi transaksi yang diperbarui karena telah melewati batas edit 24 jam.');
        }

        $transaction->update($request->validated());

        return redirect()->route('transactions.index')
            ->with('success', 'Transaksi berhasil diperbarui.');
    }

    public function destroy(FinancialTransaction $transaction)
    {
        $transaction->delete();

        return redirect()->route('transactions.index')
            ->with('success', 'Transaksi berhasil dihapus.');
    }
}
