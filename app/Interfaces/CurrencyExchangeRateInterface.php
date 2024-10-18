<?php

namespace App\Interfaces;

interface CurrencyExchangeRateInterface
{
    public function refreshExchangeRates(): array|\Exception;
}
