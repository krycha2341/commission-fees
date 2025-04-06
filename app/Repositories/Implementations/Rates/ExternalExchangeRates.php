<?php

declare(strict_types=1);

namespace App\Repositories\Implementations\Rates;

use App\Repositories\RatesRepository;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class ExternalExchangeRates implements RatesRepository
{
    private const API_URL = 'https://api.exchangeratesapi.io/v1/';

    /**
     * Its here due to lack of database/cache to store it and limits being set on calls for free API tier
     */
    private Collection $rates;

    public function __construct(
        private Client $client,
    ) {
        $this->rates = new Collection();
    }

    /**
     * @inheritDoc
     */
    public function getRates(string $currency): Collection
    {
        return collect(['USD' => 1.088519, 'EUR' => 1, 'JPY' => 158.395228]);
        $this->validateCurrency($currency);

        if (empty($this->rates->get($currency))) {
            $this->fetchRates($currency);
        }

        return $this->rates->get($currency);
    }

    private function fetchRates(string $currency): void
    {
        $responseJson = $this->client->get(
            sprintf('%s/latest', rtrim(self::API_URL, '/')),
            [
                'query' => [
                    'access_key' => config('services.exchangerates.access_key'),
                    'base' => $currency,
                    'symbols' => implode(',', config('services.exchangerates.available_currencies')),
                ],
            ],
        )->getBody()->getContents();

        $rates = json_decode($responseJson, true)['rates'];
        $this->rates->put($currency, $rates);
    }

    private function validateCurrency(string $currency): void
    {
        $availableCurrencies = config('services.exchangerates.available_currencies');

        if (!in_array($currency, $availableCurrencies, true)) {
            throw new InvalidArgumentException('Invalid currency given!');
        }
    }
}
