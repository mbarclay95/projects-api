<?php

namespace Database\Factories\Drafts;

use App\Models\Drafts\DraftPick;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DraftPick>
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
