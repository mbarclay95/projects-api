<?php

namespace Database\Factories\Drafts;

use App\Models\Drafts\DraftTeam;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DraftTeam>
 */
class DraftTeamFactory extends Factory
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
            'sort_order' => 0,
        ];
    }
}
