<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Dimension;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Dimension>
 */
class DimensionFactory extends Factory
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
            'code' => strtoupper(fake()->unique()->lexify('????')),
            'name' => fake()->unique()->words(2, true),
        ];
    }
}
