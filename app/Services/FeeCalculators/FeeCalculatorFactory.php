<?php

declare(strict_types=1);

namespace App\Services\FeeCalculators;

use Exception;
use Illuminate\Support\Facades\App;

class FeeCalculatorFactory
{
    public static function make(string $userType): FeeCalculator
    {
        $calculatorClass = config('services.fee_calculators.map')[$userType] ?? null;

        switch ($calculatorClass) {
            case null:
                throw new Exception("Unsupported user type");
            default:
                return App::make($calculatorClass);
        }
    }
}
