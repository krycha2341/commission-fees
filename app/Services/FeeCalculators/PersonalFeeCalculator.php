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

        $transactions = $this->filterOutIrrelevantTransactionsAndOrderByDate(
            $transaction,
            $transactions,
            $config['fee_free_transaction_types'],
        );
        $this->exchangeCurrencies($transactions, $config['fee_currency']);
        $this->exchangeCurrency($transaction, $config['fee_currency']);
        if (in_array($transaction->get('transaction_type'), $config['fee_free_transaction_types'])) {
            return 0.0;
        }

        $amount = $this->getAmountToApplyCommission($transaction, $transactions);

        return $this->roundAmount($amount * (float)$config['fee_rate']);
    }

    private function getAmountToApplyCommission(Collection $transaction, Collection $transactions): float
    {
        $transactionsCounter = 0;
        $withdrawAmountSum = 0;
        foreach ($transactions as $otherTransaction) {
            $withdrawAmountSum += $otherTransaction->get('amount');
            $transactionsCounter++;

            if ($transactionsCounter > static::NUMBER_OF_TRANSACTIONS_FEE_FREE) {
                return (float)$transaction->get('amount');
            }
            if ($withdrawAmountSum > static::FEE_FREE_LIMIT) {
                if ($transaction->get('id') === $otherTransaction->get('id')) {
                    return $withdrawAmountSum - static::FEE_FREE_LIMIT;
                } else {
                    return (float)$transaction->get('amount');
                }
            } elseif ($transaction->get('id') === $otherTransaction->get('id')) {
                break;
            }
        }

        return 0.0;
    }

    private function filterOutIrrelevantTransactionsAndOrderByDate(
        Collection $transaction,
        Collection $transactions,
        array $feeFreeTransactionTypes,
    ): Collection {
        $date = Carbon::createFromFormat('Y-m-d', $transaction->get('date'));
        $userId = $transaction->get('user_id');

        return $transactions->filter(function ($filteredTransaction) use ($date, $userId, $feeFreeTransactionTypes) {
            $transactionDate = Carbon::createFromFormat('Y-m-d', $filteredTransaction->get('date'));

            return $filteredTransaction->get('user_id') === $userId
                && $transactionDate->between((clone $date)->startOfWeek(), (clone $date)->endOfWeek())
                && !in_array($filteredTransaction->get('transaction_type'), $feeFreeTransactionTypes);
        })->sortBy(fn ($sortedTransaction) => Carbon::createFromFormat('Y-m-d', $sortedTransaction->get('date')));
    }

    private function exchangeCurrencies(Collection $transactions, string $toCurrency): void
    {
        /** @var Collection $transaction */
        foreach ($transactions as $transaction) {
            if ($transaction->get('currency') === $toCurrency) {
                continue;
            }

            $this->exchangeCurrency($transaction, $toCurrency);
        }
    }

    private function exchangeCurrency(Collection $transaction, string $toCurrency): void
    {
        $transaction->put(
            'amount',
            (string)$this->currencyExchanger->exchange(
                (float)$transaction->get('amount'),
                (string)$transaction->get('currency'),
                $toCurrency,
            )
        );
        $transaction->put('currency', $toCurrency);
    }
}
