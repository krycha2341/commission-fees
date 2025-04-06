<?php

declare(strict_types=1);

namespace App\Repositories\Implementations\CsvReaders;

use App\Repositories\CsvReaderRepository;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use League\Csv\Reader;
use League\Csv\Statement;

class LocalFileCsvReader implements CsvReaderRepository
{
    /**
     * @inheritDoc
     */
    public function read(string $path,  array $headers): Collection
    {
        if (!file_exists($path)) {
            throw new InvalidArgumentException("CSV file does not exist");
        }

        $csv = Reader::createFromPath($path);
        $statement = new Statement();
        $records = $statement->process($csv, $headers);

        $collection = collect();

        foreach ($records as $record) {
            $collection->push(collect($record));
        }

        return $collection;
    }
}
