<?php

namespace Database\Factories\UserGroups;

use App\Enums\FamilyTaskStrategyEnum;
use App\Models\UserGroups\UserGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserGroup>
 */
class UserGroupFactory extends Factory
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
            'config' => ['task_strategy' => FamilyTaskStrategyEnum::PER_TASK_POINT->value, 'task_points' => []],
            'scope' => 'tasks',
        ];
    }
}
