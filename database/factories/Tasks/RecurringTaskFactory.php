<?php

namespace Database\Factories\Tasks;

use App\Models\Tasks\RecurringTask;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecurringTask>
 */
class RecurringTaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => fake()->name,
            'description' => null,
            'frequency_amount' => 1,
            'frequency_unit' => 'day',
            'is_active' => true,
            'priority' => 0,
            'task_point' => 1,
        ];
    }
}
