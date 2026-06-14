<?php

namespace Database\Factories;

use App\Models\Process;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Process>
 */
class ProcessFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'             => $this->faker->sentence(3),
            'description'      => $this->faker->paragraph(),
            'frequency'        => $this->faker->randomElement(['hourly', 'daily', 'weekly', 'monthly', 'manual']),
            'status'           => 'active',
            'webhook_url'      => null,
            'executions_count' => 0,
            'success_count'    => 0,
        ];
    }
}
