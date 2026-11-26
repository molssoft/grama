<?php

namespace Database\Factories;

use App\Enums\AccountType;
use App\Models\Account;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Account>
 */
class AccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'parent_id' => null,
            'code' => fake()->unique()->numerify('9####'),
            'name' => fake()->unique()->words(3, true),
            'type' => fake()->randomElement(AccountType::cases()),
            'is_postable' => true,
            'is_active' => true,
        ];
    }

    /**
     * An account that groups others and cannot be posted to.
     */
    public function heading(): static
    {
        return $this->state(fn (): array => ['is_postable' => false]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
