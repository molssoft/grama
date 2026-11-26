<?php

namespace App\Models;

use Database\Factories\GeneralLedgerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

#[Fillable(['company_id', 'created_by', 'number', 'date', 'reference', 'description'])]
class GeneralLedger extends Model
{
    /** @use HasFactory<GeneralLedgerFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date:Y-m-d',
        ];
    }

    /**
     * Whether every group of lines has equal debits and credits.
     */
    public function isBalanced(): bool
    {
        return $this->lines()
            ->reorder()
            ->select('group_id')
            ->groupBy('group_id')
            ->havingRaw('sum(debit) <> sum(credit)')
            ->doesntExist();
    }

    /**
     * The entry as the form shows it: one row per group made of a single debit line and a single credit line.
     *
     * @return list<array{debit_account_id: int, credit_account_id: int, description: ?string, amount: string, dimensions: array<int, int>}>
     */
    public function pairRows(): array
    {
        $this->loadMissing('lines.dimensionValues');

        return $this->lines->groupBy('group_id')->map(function (Collection $group): array {
            $debit = $group->first(fn (GeneralLedgerLine $line): bool => bccomp((string) $line->debit, '0', 2) > 0);
            $credit = $group->first(fn (GeneralLedgerLine $line): bool => bccomp((string) $line->credit, '0', 2) > 0);

            return [
                'debit_account_id' => $debit?->account_id,
                'credit_account_id' => $credit?->account_id,
                'description' => $debit?->description,
                'amount' => $debit?->debit,
                'dimensions' => $debit?->dimensionValues->pluck('id', 'pivot.dimension_id')->all() ?? [],
            ];
        })->values()->all();
    }

    /**
     * Replaces all lines with one debit and one credit line per row, the two sharing a group id.
     *
     * Both lines of a row get the row's dimension values, given as dimension id => value id (empty ones are skipped).
     *
     * @param  list<array{debit_account_id: int|string, credit_account_id: int|string, description?: ?string, amount: int|float|string, dimensions?: array<int|string, int|string|null>}>  $rows
     */
    public function replacePairRows(array $rows): void
    {
        DB::transaction(function () use ($rows): void {
            $this->lines()->delete();

            foreach (array_values($rows) as $index => $row) {
                $shared = ['group_id' => $index + 1, 'description' => $row['description'] ?? null];

                $lines = [
                    $this->lines()->create($shared + ['account_id' => $row['debit_account_id'], 'debit' => $row['amount'], 'credit' => 0]),
                    $this->lines()->create($shared + ['account_id' => $row['credit_account_id'], 'debit' => 0, 'credit' => $row['amount']]),
                ];

                $valueIds = array_filter($row['dimensions'] ?? []);

                if ($valueIds === []) {
                    continue;
                }

                $values = DimensionValue::query()->with('dimension')->whereKey($valueIds)->get();

                foreach ($lines as $line) {
                    foreach ($values as $value) {
                        if ((int) $value->dimension?->company_id !== (int) $this->company_id) {
                            throw new InvalidArgumentException('A dimension value must belong to the entry\'s company.');
                        }
                    }

                    $line->dimensionValues()->attach($values->mapWithKeys(fn (DimensionValue $value): array => [
                        $value->getKey() => ['dimension_id' => $value->dimension_id],
                    ])->all());
                }
            }
        });

        $this->unsetRelation('lines');
    }

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return HasMany<GeneralLedgerLine, $this>
     */
    public function lines(): HasMany
    {
        return $this->hasMany(GeneralLedgerLine::class)->orderBy('group_id')->orderBy('id');
    }
}
