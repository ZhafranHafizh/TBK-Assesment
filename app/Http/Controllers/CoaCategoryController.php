<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCoaCategoryRequest;
use App\Http\Requests\UpdateCoaCategoryRequest;
use App\Models\CoaCategory;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\FinancialTransaction;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CoaCategoryController extends Controller
{
    public function index()
    {
        $categories = CoaCategory::latest()->paginate(10);
        return Inertia::render('CoaCategory/Index', [
            'categories' => $categories
        ]);
    }

    public function create()
    {
        return Inertia::render('CoaCategory/Create');
    }

    public function store(StoreCoaCategoryRequest $request)
    {
        CoaCategory::create($request->validated());

        return redirect()->route('coa-categories.index')
            ->with('success', 'Kategori COA berhasil ditambahkan.');
    }

    public function edit(CoaCategory $coaCategory)
    {
        return Inertia::render('CoaCategory/Edit', [
            'category' => $coaCategory
        ]);
    }

    public function update(UpdateCoaCategoryRequest $request, CoaCategory $coaCategory)
    {
        if (!$coaCategory->is_editable_full) {
            abort(403, 'Kategori COA tidak dapat diedit setelah 24 jam dibuat.');
        }

        $coaCategory->update($request->validated());

        return redirect()->route('coa-categories.index')
            ->with('success', 'Kategori COA berhasil diperbarui.');
    }

    public function destroy(CoaCategory $coaCategory)
    {
        $hasCoas = $coaCategory->coas()->exists();
        $hasTransactions = $coaCategory->coas()->whereHas('financialTransactions')->exists();

        $downloadUrl = null;

        if ($hasCoas && $hasTransactions) {
            $transactions = FinancialTransaction::with(['coa.coaCategory'])
                ->whereHas('coa', function($q) use ($coaCategory) {
                    $q->where('coa_category_id', $coaCategory->id);
                })
                ->orderBy('transaction_date')
                ->get();
                
            $startDate = $transactions->first() ? \Carbon\Carbon::parse($transactions->first()->transaction_date) : clone $coaCategory->created_at;
            $endDate = $transactions->last() ? \Carbon\Carbon::parse($transactions->last()->transaction_date) : now();
            
            $fileName = 'Laporan_Keuangan_Backup_' . Str::slug($coaCategory->name) . '_' . time() . '.pdf';
            $filePath = 'reports/' . $fileName;

            $pdf = Pdf::loadView('reports.financial_audit', [
                'startDate' => $startDate->format('Y-m-d'),
                'endDate' => $endDate->format('Y-m-d'),
                'transactions' => $transactions,
                'deletedBy' => auth()->user() ? auth()->user()->email : 'Sistem',
                'deletedDate' => now()->format('d/m/Y'),
                'deletedTime' => now()->format('H:i:s'),
                'categoryName' => $coaCategory->name
            ])->setPaper('a4', 'landscape');
            
            Storage::disk('public')->put($filePath, $pdf->output());
            
            $downloadUrl = asset('storage/' . $filePath);
        }

        // Cascade soft delete
        if ($hasCoas) {
            foreach($coaCategory->coas as $coa) {
                $coa->financialTransactions()->delete();
                $coa->delete();
            }
        }

        $coaCategory->delete();

        return redirect()->route('coa-categories.index')
            ->with('success', 'Kategori COA berhasil dihapus' . ($downloadUrl ? ' dan laporan otomatis digenerate.' : '.'))
            ->with('downloadUrl', $downloadUrl);
    }
}
