<?php

declare(strict_types=1);

namespace App\Services\FeeCalculators;

use App\Services\CurrencyExchanger;

class FeeCalculatorBase
{
    public function __construct(
        protected CurrencyExchanger $currencyExchanger,
    ) {
    }

    protected function roundAmount(float $amount): float
    {
        return (float)number_format(ceil($amount * 100) / 100, 2, '.', '');
    }
}
