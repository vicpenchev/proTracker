<?php

namespace App\Services;

use App\Interfaces\CurrencyExchangeRateInterface;
use App\Models\Currency;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ExchangeRateApiService implements CurrencyExchangeRateInterface
{
    protected $apiUrl;
    protected $apiKey;
    protected $baseCurrency;

    public function __construct()
    {
        $this->apiUrl = config('currency-exchange-rate.exchange_rate_api.url');
        $this->apiKey = config('currency-exchange-rate.exchange_rate_api.api_key');
        $this->baseCurrency = config('currency-exchange-rate.exchange_rate_api.base_currency');
    }

    public function refreshExchangeRates(): array|\Exception
    {
        $existingRates = [];
        try {
            $currencies = [];
            $response = Http::get("{$this->apiUrl}{$this->apiKey}/latest/{$this->baseCurrency}");
            if (count($response->json()['conversion_rates'])) {
                foreach ($response->json()['conversion_rates'] as $currency => $rate) {
                    if ($currency != $this->baseCurrency) {
                        $currencies[] = ['code' => $currency, 'rate' => $rate];
                    }
                }

                $existingCurrencies = Currency::pluck('code')->toArray();

                foreach ($currencies as $currency) {
                    if (in_array($currency['code'], $existingCurrencies)) {
                        $existingRates[$currency['code']] = $currency['rate'];
                    }
                }

                $query = "UPDATE currencies SET updated_at = now(), conversion_rates = CASE code ";
                foreach ($existingRates as $currencyCode => $currencyRate) {
                    $query .= "WHEN '$currencyCode' THEN $currencyRate ";
                }
                $query .= "END WHERE code IN ('" . implode("','", array_keys($existingRates)) . "')";

                DB::statement($query);
            }
        } catch (\Exception $e) {
        }
        return $existingRates;
    }
}
