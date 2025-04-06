<?php

declare(strict_types=1);

namespace App\Repositories;

use Illuminate\Support\Collection;

interface RatesRepository
{
    /**
     * @return Collection<string, float|int>
     */
    public function getRates(string $currency): Collection;
}
