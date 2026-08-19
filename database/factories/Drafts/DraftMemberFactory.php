<?php

namespace Database\Factories\Drafts;

use App\Models\Drafts\DraftMember;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<DraftMember>
 */
class DraftMemberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * pick_position is left unset, which is the state a member is in during
     * signup, before the admin has ordered the roster.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => fake()->name,
            'secret' => Str::random(32),
        ];
    }
}
