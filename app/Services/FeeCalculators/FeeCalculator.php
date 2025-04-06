<?php

declare(strict_types=1);

namespace App\Services\FeeCalculators;

use Illuminate\Support\Collection;

interface FeeCalculator
{
    public function calculateFee(Collection $transaction, ?Collection $transactions = null): float;
}
