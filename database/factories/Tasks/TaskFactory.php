<?php

namespace Database\Factories\Tasks;

use App\Models\Tasks\Family;
use App\Models\Tasks\Task;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => 'testing',
            'description' => null,
            'owner_type' => Family::class,
            'owner_id' => 1,
            'due_date' => Carbon::now(),
            'task_point' => null,
            'priority' => 0,
            'completed_at' => null,
            'cleared_at' => null,
        ];
    }
}
