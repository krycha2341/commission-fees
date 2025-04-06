<?php

declare(strict_types=1);

namespace App\Repositories;

use Illuminate\Support\Collection;

interface CsvReaderRepository
{
    /**
     * @param string[] $headers
     *
     * @return Collection<Collection<string, mixed>>
     */
    public function read(string $path, array $headers): Collection;
}
