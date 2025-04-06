<?php

declare(strict_types=1);

namespace App\Services\FeeCalculators;

use Illuminate\Support\Collection;

class BusinessFeeCalculator extends FeeCalculatorBase implements FeeCalculator
{
    public function calculateFee(Collection $transaction, ?Collection $transactions = null): float
    {
        $config = config('services.fee_calculators.business');

        if (in_array($transaction->get('transaction_type'), $config['fee_free_transaction_types'])) {
            return 0.0;
        }

        $amount = (float)$transaction->get('amount');
        if ($transaction->get('currency') !== $config['fee_currency']) {
            $amount = $this->currencyExchanger->exchange(
                $amount,
                (string)$transaction->get('currency'),
                $config['fee_currency'],
            );
        }

        return $this->roundAmount($amount * (float)$config['fee_rate']);
    }
}
