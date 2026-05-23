<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\AllowedCurrency;
use App\Models\ExchangeRate;
use App\Services\ExchangeRateService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingController extends Controller
{
    public function index(ExchangeRateService $rateService)
    {
        $settings = [
            'app_name' => AppSetting::get('app_name', 'ArthaLedger'),
            'base_currency' => AppSetting::get('base_currency', 'IDR'),
        ];

        $currencies = AllowedCurrency::with('latestRate')->get();

        $lastFetchTime = $rateService->getLastFetchTime();
        $isRateStale = $rateService->isRateStale();

        return Inertia::render('Settings/Index', [
            'settings' => $settings,
            'currencies' => $currencies,
            'lastFetchTime' => $lastFetchTime?->toIso8601String(),
            'isRateStale' => $isRateStale,
        ]);
    }

    public function updateAppName(Request $request)
    {
        $request->validate([
            'app_name' => 'required|string|max:100',
        ]);

        AppSetting::set('app_name', $request->input('app_name'));

        return back()->with('success', 'Nama aplikasi berhasil diperbarui.');
    }

    public function addCurrency(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:3|unique:allowed_currencies,code',
            'name' => 'required|string|max:100',
        ]);

        AllowedCurrency::create([
            'code' => strtoupper($request->input('code')),
            'name' => $request->input('name'),
        ]);

        return back()->with('success', "Mata uang {$request->input('code')} berhasil ditambahkan.");
    }

    public function removeCurrency($id)
    {
        $currency = AllowedCurrency::findOrFail($id);
        $code = $currency->code;

        // Also clean up cached rates for this currency
        ExchangeRate::where('currency_code', $code)->delete();
        $currency->delete();

        return back()->with('success', "Mata uang $code berhasil dihapus.");
    }

    public function fetchRates(ExchangeRateService $rateService)
    {
        $result = $rateService->fetchLatestRates();

        return back()->with(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
    }

    public function fetchHistoricalRate(Request $request, ExchangeRateService $rateService)
    {
        $request->validate([
            'currency' => 'required|string|size:3',
            'date' => 'required|date|date_format:Y-m-d',
        ]);

        $rate = $rateService->getHistoricalRate($request->input('currency'), $request->input('date'));

        if ($rate === null) {
            return response()->json(['success' => false, 'message' => 'Gagal mengambil rate historis.'], 500);
        }

        return response()->json(['success' => true, 'rate' => $rate]);
    }
}
