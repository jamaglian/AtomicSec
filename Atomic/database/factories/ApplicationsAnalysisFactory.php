<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ApplicationsAnalysis>
 */
class ApplicationsAnalysisFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'application_id' => 0,
            'analysis' => json_encode(array()),
            'log'  => fake()->text()
        ];
    }
}
