<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Exports\ProfitLossExport;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index(Request $request)
    {
        $month = $request->input('month', date('n'));
        $year = $request->input('year', date('Y'));

        $data = $this->reportService->getProfitLoss((int)$month, (int)$year);

        return Inertia::render('Report/Index', [
            'reportData' => $data,
            'filters' => [
                'month' => (int)$month,
                'year' => (int)$year
            ]
        ]);
    }

    public function export(Request $request)
    {
        $month = $request->input('month', date('n'));
        $year = $request->input('year', date('Y'));

        $data = $this->reportService->getProfitLoss((int)$month, (int)$year);
        
        $monthPadded = str_pad($month, 2, '0', STR_PAD_LEFT);
        $filename = "Profit_Loss_Report_{$year}_{$monthPadded}.xlsx";

        return Excel::download(new ProfitLossExport($data), $filename);
    }
}
