<?php

declare(strict_types=1);

namespace Tests\Functional;

use App\Repositories\RatesRepository;
use App\Services\CommissionFeesCalculator;
use Illuminate\Support\Collection;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CommissionFeesCalculationTest extends TestCase
{
    #[DataProvider('calculationDataProvider')]
    public function testCommissionFeesCalculation(Collection $transactions): void
    {
        $mock = Mockery::mock(RatesRepository::class);
        $mock->shouldReceive('getRates')
            ->andReturn(collect([
                'USD' => 1.088519,
                'JPY' => 158.395228,
                'EUR' => 1,
            ]));

        $this->app->instance(RatesRepository::class, $mock);
        $results = $this->createInstance()->calculateCommissionFees($transactions);

        $this->assertEquals(collect([
            6,
            30,
            0,
            0,
            15,
            0,
            5.69,
            2.76,
            3,
            0,
            0,
            0,
            538.2,
        ]), $results, '');
    }

    public static function calculationDataProvider(): array
    {
        return [
            [
                collect([
                    collect(['date' => '2014-12-31', 'user_id' => 4, 'user_type' => 'private', 'transaction_type' => 'withdraw', 'amount' => 1200.00, 'currency' => 'EUR']),
                    collect(['date' => '2015-01-01', 'user_id' => 4, 'user_type' => 'private', 'transaction_type' => 'withdraw', 'amount' => 1000.00, 'currency' => 'EUR']),
                    collect(['date' => '2016-01-05', 'user_id' => 4, 'user_type' => 'private', 'transaction_type' => 'withdraw', 'amount' => 1000.00, 'currency' => 'EUR']),
                    collect(['date' => '2016-01-05', 'user_id' => 1, 'user_type' => 'private', 'transaction_type' => 'deposit', 'amount' => 200.00, 'currency' => 'EUR']),
                    collect(['date' => '2016-01-06', 'user_id' => 2, 'user_type' => 'business', 'transaction_type' => 'withdraw', 'amount' => 300.00, 'currency' => 'EUR']),
                    collect(['date' => '2016-01-06', 'user_id' => 1, 'user_type' => 'private', 'transaction_type' => 'withdraw', 'amount' => 30000, 'currency' => 'JPY']),
                    collect(['date' => '2016-01-07', 'user_id' => 1, 'user_type' => 'private', 'transaction_type' => 'withdraw', 'amount' => 1000.00, 'currency' => 'EUR']),
                    collect(['date' => '2016-01-07', 'user_id' => 1, 'user_type' => 'private', 'transaction_type' => 'withdraw', 'amount' => 100.00, 'currency' => 'USD']),
                    collect(['date' => '2016-01-10', 'user_id' => 1, 'user_type' => 'private', 'transaction_type' => 'withdraw', 'amount' => 100.00, 'currency' => 'EUR']),
                    collect(['date' => '2016-01-10', 'user_id' => 2, 'user_type' => 'business', 'transaction_type' => 'deposit', 'amount' => 10000.00, 'currency' => 'EUR']),
                    collect(['date' => '2016-01-10', 'user_id' => 3, 'user_type' => 'private', 'transaction_type' => 'withdraw', 'amount' => 1000.00, 'currency' => 'EUR']),
                    collect(['date' => '2016-02-15', 'user_id' => 1, 'user_type' => 'private', 'transaction_type' => 'withdraw', 'amount' => 300.00, 'currency' => 'EUR']),
                    collect(['date' => '2016-02-19', 'user_id' => 5, 'user_type' => 'private', 'transaction_type' => 'withdraw', 'amount' => 3000000, 'currency' => 'JPY']),
                ])
            ]
        ];
    }

    private function createInstance(): CommissionFeesCalculator
    {
        return new CommissionFeesCalculator();
    }
}
