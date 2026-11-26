<?php

namespace App\Console\Commands;

use App\Models\Currency;
use App\Models\CurrencyRate;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use SimpleXMLElement;

#[Signature('currency-rates:import-ecb {--period=daily : daily, 90d or all}')]
#[Description('Import the global EUR reference rates published by the European Central Bank')]
class ImportEcbRates extends Command
{
    public const SOURCE = 'ecb';

    /**
     * @var array<string, string>
     */
    public const FEEDS = [
        'daily' => 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml',
        '90d' => 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-hist-90d.xml',
        'all' => 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-hist.xml',
    ];

    public function handle(): int
    {
        $period = (string) $this->option('period');

        if (! array_key_exists($period, self::FEEDS)) {
            $this->error('The period must be one of: '.implode(', ', array_keys(self::FEEDS)).'.');

            return self::INVALID;
        }

        try {
            $rates = $this->parse($this->download(self::FEEDS[$period]));
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        [$created, $updated] = $this->store($rates);

        $this->info("ECB rates imported: {$created} created, {$updated} updated.");

        return self::SUCCESS;
    }

    private function download(string $url): string
    {
        $response = Http::timeout(30)->retry(3, 1000, throw: false)->get($url);

        if ($response->failed()) {
            throw new RuntimeException("Could not download the ECB rates (HTTP {$response->status()}).");
        }

        return $response->body();
    }

    /**
     * @return list<array{date: string, code: string, rate: string}>
     */
    private function parse(string $xml): array
    {
        $previous = libxml_use_internal_errors(true);
        $document = simplexml_load_string($xml);
        libxml_use_internal_errors($previous);

        if (! $document instanceof SimpleXMLElement) {
            throw new RuntimeException('The ECB response is not valid XML.');
        }

        $document->registerXPathNamespace('e', 'http://www.ecb.int/vocabulary/2002-08-01/eurofxref');
        $rates = [];

        foreach ($document->xpath('//e:Cube[@time]') ?: [] as $day) {
            $day->registerXPathNamespace('e', 'http://www.ecb.int/vocabulary/2002-08-01/eurofxref');

            foreach ($day->xpath('e:Cube[@currency]') ?: [] as $cube) {
                $rates[] = [
                    'date' => (string) $day['time'],
                    'code' => (string) $cube['currency'],
                    'rate' => (string) $cube['rate'],
                ];
            }
        }

        if ($rates === []) {
            throw new RuntimeException('The ECB response contains no rates.');
        }

        return $rates;
    }

    /**
     * Stores global rates for the currencies we know. Rates entered by hand are left untouched.
     *
     * @param  list<array{date: string, code: string, rate: string}>  $rates
     * @return array{0: int, 1: int} created and updated counts
     */
    private function store(array $rates): array
    {
        $currencyIds = Currency::query()->pluck('id', 'code');
        $existing = CurrencyRate::query()
            ->whereNull('company_id')
            ->whereDate('date', '>=', min(array_column($rates, 'date')))
            ->whereDate('date', '<=', max(array_column($rates, 'date')))
            ->get()
            ->keyBy(fn (CurrencyRate $rate): string => $rate->currency_id.'|'.$rate->date->toDateString());

        $new = [];
        $updated = 0;

        foreach ($rates as $row) {
            $currencyId = $currencyIds[$row['code']] ?? null;

            if ($currencyId === null) {
                continue;
            }

            $current = $existing[$currencyId.'|'.$row['date']] ?? null;

            if ($current === null) {
                $new[] = [
                    'company_id' => null,
                    'currency_id' => $currencyId,
                    'date' => $row['date'],
                    'rate' => $row['rate'],
                    'source' => self::SOURCE,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            } elseif ($current->source === self::SOURCE && bccomp($current->rate, $row['rate'], CurrencyRate::SCALE) !== 0) {
                $current->update(['rate' => $row['rate']]);
                $updated++;
            }
        }

        foreach (array_chunk($new, 1000) as $chunk) {
            CurrencyRate::query()->insert($chunk);
        }

        return [count($new), $updated];
    }
}
