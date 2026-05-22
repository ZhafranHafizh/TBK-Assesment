<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCoaRequest;
use App\Http\Requests\UpdateCoaRequest;
use App\Models\Coa;
use App\Models\CoaCategory;
use Inertia\Inertia;

class CoaController extends Controller
{
    public function index()
    {
        $coas = Coa::with('coaCategory')->latest()->paginate(10);
        $categories = CoaCategory::all();
        
        return Inertia::render('Coa/Index', [
            'coas' => $coas,
            'categories' => $categories
        ]);
    }

    public function create()
    {
        $categories = CoaCategory::all();
        return Inertia::render('Coa/Create', [
            'categories' => $categories
        ]);
    }

    public function store(StoreCoaRequest $request)
    {
        Coa::create($request->validated());

        return redirect()->route('coas.index')
            ->with('success', 'Chart of Account berhasil ditambahkan.');
    }

    public function edit(Coa $coa)
    {
        $categories = CoaCategory::all();
        return Inertia::render('Coa/Edit', [
            'coa' => $coa,
            'categories' => $categories
        ]);
    }

    public function update(UpdateCoaRequest $request, Coa $coa)
    {
        if (!$coa->is_editable_full) {
            abort(403, 'COA tidak dapat diedit setelah 24 jam dibuat.');
        }

        $coa->update($request->validated());

        return redirect()->route('coas.index')
            ->with('success', 'Chart of Account berhasil diperbarui.');
    }

    public function destroy(Coa $coa)
    {
        $coa->financialTransactions()->delete();
        
        $coa->delete();

        return redirect()->route('coas.index')
            ->with('success', 'Chart of Account berhasil dihapus.');
    }
}
