<?php

namespace Database\Factories\Drafts;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Drafts\DraftPick>
 */
class DraftPickFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'pick_number' => 1,
            'made_by_admin' => false,
        ];
    }
}
