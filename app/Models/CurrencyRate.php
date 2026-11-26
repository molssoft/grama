<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\CurrencyRateFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['company_id', 'currency_id', 'date', 'rate', 'source'])]
class CurrencyRate extends Model
{
    /** @use HasFactory<CurrencyRateFactory> */
    use HasFactory;

    /**
     * All rates are quoted against this currency: 1 EUR = `rate` units of the rated currency.
     */
    public const PIVOT_CURRENCY_CODE = 'EUR';

    public const SCALE = 8;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date:Y-m-d',
            'rate' => 'decimal:8',
        ];
    }

    /**
     * Units of the currency per 1 EUR on the given date.
     *
     * Uses the most recent rate on or before the date, so weekends and holidays fall back to the last
     * published rate. A company's override wins over the global rate of the same date, but an older
     * override does not hide a newer global rate.
     */
    public static function rateAgainstPivot(Currency $currency, CarbonInterface $date, ?Company $company = null): ?string
    {
        if ($currency->code === self::PIVOT_CURRENCY_CODE) {
            return bcadd('1', '0', self::SCALE);
        }

        $rate = static::query()
            ->where('currency_id', $currency->getKey())
            ->whereDate('date', '<=', $date)
            ->where(fn ($query) => $query->whereNull('company_id')->when(
                $company !== null,
                fn ($query) => $query->orWhere('company_id', $company->getKey()),
            ))
            ->orderByDesc('date')
            ->orderByRaw('company_id is null')
            ->first();

        return $rate?->rate;
    }

    /**
     * How many units of `$to` one unit of `$from` is worth on the given date, or null when a rate is missing.
     */
    public static function rateBetween(Currency $from, Currency $to, CarbonInterface $date, ?Company $company = null): ?string
    {
        if ($from->is($to)) {
            return bcadd('1', '0', self::SCALE);
        }

        $fromRate = static::rateAgainstPivot($from, $date, $company);
        $toRate = static::rateAgainstPivot($to, $date, $company);

        if ($fromRate === null || $toRate === null || bccomp($fromRate, '0', self::SCALE) === 0) {
            return null;
        }

        return bcdiv($toRate, $fromRate, self::SCALE);
    }

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * @return BelongsTo<Currency, $this>
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }
}
