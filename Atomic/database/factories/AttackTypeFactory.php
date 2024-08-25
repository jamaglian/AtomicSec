<?php

namespace Database\Factories;

use App\Models\AttackType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AttackType>
 */
class AttackTypeFactory extends Factory
{
    protected $model = AttackType::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'params' => $this->faker->randomElement([null, json_encode(['key' => 'value'])]),
        ];
    }
}
