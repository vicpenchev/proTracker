<?php

namespace App\Filament\Helpers;

use App\Models\Currency;

final class CurrencyHelper {

    /**
     * @param float $amount
     * @param string $fromCurrency
     * @param string $toCurrency
     * @return float
     */
    public static function convert(float $amount, int $fromCurrency, int $toCurrency): float
    {
        if ($fromCurrency === $toCurrency) {
            return $amount;
        }

        $rates = Currency::query()->whereIn('id', [$fromCurrency, $toCurrency])->get();


        if ($rates->isNotEmpty()) {
            $fromConversionRate = self::getConversionRate($rates, $fromCurrency) ?? 1;
            $toConversionRate = self::getConversionRate($rates, $toCurrency) ?? 1;
        } else {
            return $amount;
        }

        $amountInBase = $amount / $fromConversionRate;
        return $amountInBase * $toConversionRate;
    }

    private static function getConversionRate($rates, int $currencyId): ?float
    {
        foreach ($rates as $rate) {
            if ($currencyId === $rate->id) {
                return $rate->conversion_rates;
            }
        }
        return null;
    }
}
