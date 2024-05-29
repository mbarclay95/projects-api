<?php

namespace Database\Factories\Tasks;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tasks\TaskUserConfig>
 */
class TaskUserConfigFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'tasks_per_week' => 5,
            'default_tasks_per_week' => 5,
            'start_date' => Carbon::now()->startOfWeek()->toDateString(),
            'end_date' => Carbon::now()->endOfWeek()->toDateString(),
            'family_id' => 1,
            'user_id' => 1
        ];
    }
}
