<?php

namespace Database\Factories\Tasks;

use App\Enums\FamilyTaskStrategyEnum;
use App\Models\Tasks\Family;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Family>
 */
class FamilyFactory extends Factory
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
            'task_strategy' => FamilyTaskStrategyEnum::PER_TASK_POINT,
        ];
    }
}
