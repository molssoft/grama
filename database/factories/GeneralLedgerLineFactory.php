<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\GeneralLedger;
use App\Models\GeneralLedgerLine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GeneralLedgerLine>
 */
class GeneralLedgerLineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'general_ledger_id' => GeneralLedger::factory(),
            'account_id' => Account::factory(),
            'group_id' => 1,
            'description' => null,
            'debit' => fake()->randomFloat(2, 1, 1000),
            'credit' => 0,
            'currency_id' => null,
            'currency_amount' => null,
            'exchange_rate' => null,
        ];
    }

    public function credit(?string $amount = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'debit' => 0,
            'credit' => $amount ?? $attributes['debit'],
        ]);
    }
}
