<?php

namespace Database\Factories;

use App\Models\AttackType;
use App\Models\Applications;
use App\Models\ApplicationAttack;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ApplicationAttack>
 */
class ApplicationAttackFactory extends Factory
{
    protected $model = ApplicationAttack::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'application_id' => Applications::factory(),
            'attacks_types_id' => AttackType::factory(),
            'attack_analysis' => json_encode([
                'risk' => $this->faker->randomElement(['low', 'medium', 'high']),
                'details' => $this->faker->sentence(),
            ]),
            'log' => $this->faker->text(200),
            'status' => $this->faker->randomElement(['Pendente', 'Em Progresso', 'Concluído']),
            'started_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'finish_at' => $this->faker->optional()->dateTimeBetween('now', '+1 month'),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
