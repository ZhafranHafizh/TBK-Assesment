<?php

namespace App\Services;

use App\Models\AllowedCurrency;
use App\Models\ExchangeRate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ExchangeRateService
{
    /**
     * Fetch latest exchange rates from Frankfurter API for all allowed currencies.
     * Rates are fetched as: 1 [FOREIGN] = ? IDR
     *
     * @return array{success: bool, message: string, fetched_count: int}
     */
    public function fetchLatestRates(): array
    {
        $currencies = AllowedCurrency::pluck('code')->toArray();

        if (empty($currencies)) {
            return [
                'success' => false,
                'message' => 'Tidak ada mata uang asing yang terdaftar.',
                'fetched_count' => 0,
            ];
        }

        $fetchedCount = 0;
        $now = Carbon::now();
        $errors = [];

        foreach ($currencies as $code) {
            try {
                // Frankfurter API: Get 1 unit of foreign currency in IDR
                $response = Http::withoutVerifying()->timeout(10)->get("https://api.frankfurter.dev/v1/latest", [
                    'from' => $code,
                    'to' => 'IDR',
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $rateToIdr = $data['rates']['IDR'] ?? null;

                    if ($rateToIdr) {
                        ExchangeRate::create([
                            'currency_code' => $code,
                            'rate_to_idr' => $rateToIdr,
                            'fetched_at' => $now,
                        ]);
                        $fetchedCount++;
                    }
                } else {
                    $errors[] = "$code (HTTP {$response->status()})";
                    Log::warning("ExchangeRate fetch failed for $code", ['status' => $response->status()]);
                }
            } catch (\Exception $e) {
                $errors[] = "$code ({$e->getMessage()})";
                Log::error("ExchangeRate fetch exception for $code", ['error' => $e->getMessage()]);
            }
        }

        $message = "Berhasil mengambil rate untuk $fetchedCount mata uang.";
        if (!empty($errors)) {
            $message .= ' Gagal: ' . implode(', ', $errors);
        }

        return [
            'success' => $fetchedCount > 0,
            'message' => $message,
            'fetched_count' => $fetchedCount,
        ];
    }

    /**
     * Get the latest cached rate for a currency code.
     */
    public function getRate(string $currencyCode): ?float
    {
        $rate = ExchangeRate::where('currency_code', $currencyCode)
            ->orderByDesc('fetched_at')
            ->first();

        return $rate ? (float) $rate->rate_to_idr : null;
    }

    /**
     * Check if the rates are stale (fetched before today).
     */
    public function isRateStale(): bool
    {
        $latestRate = ExchangeRate::orderByDesc('fetched_at')->first();

        if (!$latestRate) {
            return true; // No rates at all
        }

        return !$latestRate->fetched_at->isToday();
    }

    /**
     * Get the last fetch timestamp.
     */
    public function getLastFetchTime(): ?Carbon
    {
        $latest = ExchangeRate::orderByDesc('fetched_at')->first();
        return $latest?->fetched_at;
    }

    /**
     * Fetch historical rate for a specific date from Frankfurter API.
     * Uses Laravel Cache to prevent hitting the API repeatedly for the same date.
     */
    public function getHistoricalRate(string $currencyCode, string $date): ?float
    {
        // Cache key example: rate_USD_2026-05-20
        $cacheKey = "rate_{$currencyCode}_{$date}";

        return Cache::remember($cacheKey, 86400 * 30, function () use ($currencyCode, $date) {
            try {
                // Frankfurter API for historical rates: GET /v1/YYYY-MM-DD
                $response = Http::withoutVerifying()->timeout(10)->get("https://api.frankfurter.dev/v1/{$date}", [
                    'from' => $currencyCode,
                    'to' => 'IDR',
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    return $data['rates']['IDR'] ?? null;
                }
                
                Log::warning("Historical ExchangeRate fetch failed for $currencyCode on $date", ['status' => $response->status()]);
                return null;
            } catch (\Exception $e) {
                Log::error("Historical ExchangeRate fetch exception for $currencyCode on $date", ['error' => $e->getMessage()]);
                return null;
            }
        });
    }
}
