<?php

namespace Database\Factories\Drafts;

use App\Enums\DraftStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Drafts\Draft>
 */
class DraftFactory extends Factory
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
            'token' => Str::random(),
            'status' => DraftStatus::SIGNUP,
            'total_rounds' => 1,
        ];
    }
}
