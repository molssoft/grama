<?php

namespace App\Models;

use Database\Factories\GeneralLedgerLineFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use InvalidArgumentException;

#[Fillable(['general_ledger_id', 'account_id', 'group_id', 'description', 'debit', 'credit', 'currency_id', 'currency_amount', 'exchange_rate'])]
class GeneralLedgerLine extends Model
{
    /** @use HasFactory<GeneralLedgerLineFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::saving(function (GeneralLedgerLine $line): void {
            if (bccomp((string) $line->debit, '0', 2) < 0 || bccomp((string) $line->credit, '0', 2) < 0) {
                throw new InvalidArgumentException('Debit and credit cannot be negative.');
            }

            if (bccomp((string) $line->debit, '0', 2) > 0 && bccomp((string) $line->credit, '0', 2) > 0) {
                throw new InvalidArgumentException('A line cannot have both a debit and a credit amount.');
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'group_id' => 'integer',
            'debit' => 'decimal:2',
            'credit' => 'decimal:2',
            'currency_amount' => 'decimal:2',
            'exchange_rate' => 'decimal:8',
        ];
    }

    /**
     * @return BelongsTo<GeneralLedger, $this>
     */
    public function generalLedger(): BelongsTo
    {
        return $this->belongsTo(GeneralLedger::class);
    }

    /**
     * @return BelongsTo<Account, $this>
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * @return BelongsTo<Currency, $this>
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * The dimension values this line is tagged with, at most one per dimension.
     *
     * @return BelongsToMany<DimensionValue, $this>
     */
    public function dimensionValues(): BelongsToMany
    {
        return $this->belongsToMany(DimensionValue::class, 'general_ledger_line_dimension_values')
            ->withPivot('dimension_id')
            ->withTimestamps();
    }
}
