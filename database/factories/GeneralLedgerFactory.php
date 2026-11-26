<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\GeneralLedger;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GeneralLedger>
 */
class GeneralLedgerFactory extends Factory
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
            'created_by' => null,
            'number' => fake()->unique()->numerify('JE-######'),
            'date' => fake()->date(),
            'reference' => null,
            'description' => fake()->sentence(),
        ];
    }
}
