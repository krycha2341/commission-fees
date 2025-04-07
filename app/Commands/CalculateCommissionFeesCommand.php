<?php

declare(strict_types=1);

namespace App\Commands;

use App\Repositories\CsvReaderRepository;
use App\Services\CommissionFeesCalculator;
use Illuminate\Console\Command;

class CalculateCommissionFeesCommand extends Command
{
    private const CSV_HEADERS = [
        'date',
        'user_id',
        'user_type',
        'transaction_type',
        'amount',
        'currency',
    ];

    protected $signature = 'calculate:commission-fees {path?}';
    protected $description = 'Calculate commission fees.';

    public function handle(
        CsvReaderRepository $csvReaderRepository,
        CommissionFeesCalculator $commissionFeesCalculator,
    ): void {
        $filePath = 'app/public/transactions.csv';
        $customFilePath = $this->argument('path');
        if (!empty($customFilePath)) {
            $filePath = $customFilePath;
        }

        // here some kind of DTO/VO should be used instead of a collection to be able to easier manipulate/read data
        // and to protect data from being corrupted anywhere in the application
        $csvContent = $csvReaderRepository->read(storage_path($filePath), static::CSV_HEADERS);

        $this->output->write(
            $commissionFeesCalculator->calculateCommissionFees($csvContent),
            true,
        );
    }
}
