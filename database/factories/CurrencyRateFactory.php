<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Currency;
use App\Models\CurrencyRate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CurrencyRate>
 */
class CurrencyRateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => null,
            'currency_id' => Currency::factory(),
            'date' => fake()->date(),
            'rate' => fake()->randomFloat(8, 0.1, 200),
            'source' => 'manual',
        ];
    }

    public function forCompany(?Company $company = null): static
    {
        return $this->state(fn (): array => ['company_id' => $company?->getKey() ?? Company::factory()]);
    }
}
