<?php

namespace App\Providers;

use App\Commands\CalculateCommissionFeesCommand;
use App\Repositories\CsvReaderRepository;
use App\Repositories\Implementations\CsvReaders\LocalFileCsvReader;
use App\Repositories\Implementations\Rates\ExternalExchangeRates;
use App\Repositories\RatesRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(CsvReaderRepository::class, LocalFileCsvReader::class);
        $this->app->singleton(RatesRepository::class, ExternalExchangeRates::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->commands([
            CalculateCommissionFeesCommand::class,
        ]);
    }
}
