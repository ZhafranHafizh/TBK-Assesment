<?php

namespace App\Http\Controllers;

use App\Models\CoaCategory;
use App\Models\Coa;
use App\Models\FinancialTransaction;
use Inertia\Inertia;
use Illuminate\Http\Request;

class ArchiveController extends Controller
{
    public function index()
    {
        $categories = CoaCategory::onlyTrashed()->orderByDesc('deleted_at')->get();
        $coas = Coa::onlyTrashed()->with(['coaCategory' => fn($q) => $q->withTrashed()])->orderByDesc('deleted_at')->get();
        $transactions = FinancialTransaction::onlyTrashed()->with(['coa' => fn($q) => $q->withTrashed()->with(['coaCategory' => fn($q2) => $q2->withTrashed()])])->orderByDesc('deleted_at')->get();

        $reports = [];
        $files = \Illuminate\Support\Facades\Storage::disk('public')->files('reports');
        foreach ($files as $file) {
            $reports[] = [
                'name' => basename($file),
                'url' => asset('storage/' . $file),
                'size' => round(\Illuminate\Support\Facades\Storage::disk('public')->size($file) / 1024, 2) . ' KB',
                'last_modified' => \Carbon\Carbon::createFromTimestamp(\Illuminate\Support\Facades\Storage::disk('public')->lastModified($file))->toDateTimeString()
            ];
        }

        // Sort by last modified DESC
        usort($reports, fn($a, $b) => $b['last_modified'] <=> $a['last_modified']);

        return Inertia::render('Archive/Index', [
            'categories' => $categories,
            'coas' => $coas,
            'transactions' => $transactions,
            'reports' => $reports
        ]);
    }

    public function restore(Request $request, $type, $id)
    {
        switch ($type) {
            case 'category':
                $cat = CoaCategory::onlyTrashed()->findOrFail($id);
                
                // Cascade restore ke bawah (COAs dan Transaksinya)
                $coas = $cat->coas()->onlyTrashed()->get();
                foreach ($coas as $c) {
                    $txs = $c->financialTransactions()->onlyTrashed()->get();
                    foreach ($txs as $tx) {
                        $tx->restored_at = now();
                        $tx->restore();
                    }
                    $c->restored_at = now();
                    $c->restore();
                }
                
                $cat->restored_at = now();
                $cat->restore();
                break;
            case 'coa':
                $coa = Coa::onlyTrashed()->findOrFail($id);
                
                // Pastikan Parent utamanya juga hidup
                if ($coa->coaCategory()->withTrashed()->first()->trashed()) {
                     $cat = $coa->coaCategory()->withTrashed()->first();
                     $cat->restored_at = now();
                     $cat->restore();
                }
                
                // Cascade restore ke bawah (Transaksinya)
                $txs = $coa->financialTransactions()->onlyTrashed()->get();
                foreach ($txs as $tx) {
                    $tx->restored_at = now();
                    $tx->restore();
                }

                $coa->restored_at = now();
                $coa->restore();
                break;
            case 'transaction':
                $transaction = FinancialTransaction::onlyTrashed()->findOrFail($id);
                $coa = $transaction->coa()->withTrashed()->first();
                if ($coa && $coa->trashed()) {
                    if ($coa->coaCategory()->withTrashed()->first()->trashed()) {
                        $cat = $coa->coaCategory()->withTrashed()->first();
                        $cat->restored_at = now();
                        $cat->restore();
                    }
                    $coa->restored_at = now();
                    $coa->restore();
                }
                $transaction->restored_at = now();
                $transaction->restore();
                break;
            default:
                abort(404);
        }

        return redirect()->route('archive.index')->with('success', 'Data berhasil dipulihkan dari arsip.');
    }
}
