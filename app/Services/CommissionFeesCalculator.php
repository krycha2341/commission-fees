<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\FeeCalculators\FeeCalculatorFactory;
use App\Services\FeeCalculators\FeeCalculator;
use Illuminate\Support\Collection;

class CommissionFeesCalculator
{
    public function calculateCommissionFees(Collection $transactions): Collection
    {
        $commissionFees = collect();
        $this->setTransactionsIdentifiers($transactions);

        foreach ($transactions as $transaction) {
            $calculator = $this->getCalculator($transaction->get('user_type'));

            $commissionFees->push(
                $calculator->calculateFee(
                    // to ensure data won't get changed
                    unserialize(serialize($transaction)),
                    unserialize(serialize($transactions)),
                )
            );
        }

        return $commissionFees;
    }

    private function getCalculator(string $userType): FeeCalculator
    {
        return FeeCalculatorFactory::make($userType);
    }

    private function setTransactionsIdentifiers(Collection $transactions): void
    {
        // need to add identifiers to be able to calculate amount to apply commission fee on
        $transactions->each(function (Collection $innerTransaction, $key) {
            $innerTransaction->put('id', (int)$key + 1);
        });
    }
}
