<?php

namespace App\Providers;

use App\Interfaces\CurrencyExchangeRateInterface;
use App\Services\ExchangeRateApiService;
use Illuminate\Support\ServiceProvider;

class CurrencyExchangeRateServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(CurrencyExchangeRateInterface::class, function ($app) {
            $defaultApi = config('currency-exchange-rate.currency_converter.default');

            return match ($defaultApi) {
                default => new ExchangeRateApiService(),
            };
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
