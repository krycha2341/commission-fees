<?php

declare(strict_types=1);

namespace App\Services\FeeCalculators;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class PersonalFeeCalculator extends FeeCalculatorBase implements FeeCalculator
{
    private const FEE_FREE_LIMIT = 1000.0;
    private const NUMBER_OF_TRANSACTIONS_FEE_FREE = 3;

    /**
     * @param Collection<Collection<>>|null $transactions
     */
    public function calculateFee(Collection $transaction, ?Collection $transactions = null): float
    {
        if (null === $transactions) {
            throw new InvalidArgumentException('Can not calculate fee without user transactions.');
        }
        $config = config('services.fee_calculators.personal');

        $this->filterOutIrrelevantTransactionsAndOrderByDate(
            $transaction,
            $transactions,
            $config['fee_free_transaction_types'],
        );
        $this->exchangeCurrencies($transactions, $config['fee_currency']);

        if (
            in_array($transaction->get('transaction_type'), $config['fee_free_transaction_types'])
            || !$this->shouldApplyFee($transaction, $transactions)
        ) {
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

    private function shouldApplyFee(Collection $transaction, Collection $transactions): bool
    {
        $transactionsCounter = 0;
        $withdrawAmountSum = $transaction->get('amount');
        foreach ($transactions as $otherTransaction) {
            if (
                $transactionsCounter > static::NUMBER_OF_TRANSACTIONS_FEE_FREE
                || $withdrawAmountSum > static::FEE_FREE_LIMIT
            ) {
                return true;
            }

            if ($otherTransaction->get('id') === $transaction->get('id')) {
                return false;
            }

            $withdrawAmountSum += $otherTransaction->get('amount');
            $transactionsCounter++;
        }

        return false;
    }

    private function getAmountToApplyCommission(Collection $transaction, Collection $transactions): float
    {
        $transactionsCounter = 0;
        $withdrawAmountSum = 0;
        foreach ($transactions as $otherTransaction) {
            if ($transactionsCounter > static::NUMBER_OF_TRANSACTIONS_FEE_FREE) {
                return (float)$transaction->get('amount');
            }
            if ($withdrawAmountSum > static::FEE_FREE_LIMIT) {
                return (float)$otherTransaction->get('amount') - static::FEE_FREE_LIMIT;
            }

            $withdrawAmountSum += $otherTransaction->get('amount');
            $transactionsCounter++;
        }

        return 0.0;
    }

    private function filterOutIrrelevantTransactionsAndOrderByDate(
        Collection $transaction,
        Collection $transactions,
        array $feeFreeTransactionTypes,
    ): void {
        $date = Carbon::createFromFormat('Y-m-d', $transaction->get('date'));
        $userId = $transaction->get('user_id');
        $transactions->filter(function ($filteredTransaction) use ($date, $userId, $feeFreeTransactionTypes) {
            $transactionDate = Carbon::createFromFormat('Y-m-d', $filteredTransaction->get('date'));

            return $filteredTransaction->get('user_id') === $userId
                && $transactionDate->between($date->startOfWeek(), $date->endOfWeek())
                && in_array($filteredTransaction->get('transaction_type'), $feeFreeTransactionTypes);
        })->sortBy(fn ($sortedTransaction) => Carbon::createFromFormat('Y-m-d', $sortedTransaction->get('date')));
    }

    private function exchangeCurrencies(Collection $transactions, string $toCurrency): void
    {
        /** @var Collection $transaction */
        foreach ($transactions as $transaction) {
            if ($transaction->get('currency') === $toCurrency) {
                continue;
            }

            $transaction->put('currency', $toCurrency);
            $transaction->put(
                'amount',
                $this->currencyExchanger->exchange(
                    (float)$transaction->get('amount'),
                    (string)$transaction->get('currency'),
                    $toCurrency,
                )
            );
        }
    }
}
