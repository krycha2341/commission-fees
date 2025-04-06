<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\RatesRepository;

class CurrencyExchanger
{
    public function __construct(
        private RatesRepository $ratesRepository,
    ) {
    }

    public function exchange(float $amount, string $fromCurrency, string $toCurrency): float
    {
        $rates = $this->ratesRepository->getRates($toCurrency);

        $exchangeRate = $rates[$fromCurrency];

        return $amount / $exchangeRate;
    }
}
