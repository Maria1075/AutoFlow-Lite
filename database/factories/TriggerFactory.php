<?php

namespace Database\Factories;

use App\Models\Trigger;
use App\Models\Workflow;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Trigger>
 */
class TriggerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workflow_id' => Workflow::factory(),
            'name' => $this->faker->sentence(2),
            'type' => 'manual',
            'cron_expression' => null,
            'webhook_token' => null,
        ];
    }
}
