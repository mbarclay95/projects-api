<?php

namespace Database\Factories\Drafts;

use App\Models\Drafts\DraftAdmin;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DraftAdmin>
 */
class DraftAdminFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * Both ids are left to the caller — a draft_admins row is meaningless
     * without the pair, and unique(draft_id, user_id) makes a random default a
     * collision waiting to happen.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [];
    }
}
