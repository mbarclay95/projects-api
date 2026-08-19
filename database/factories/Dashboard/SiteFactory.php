<?php

namespace Database\Factories\Dashboard;

use App\Models\Dashboard\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Site>
 */
class SiteFactory extends Factory
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
            'sort' => 1,
            'show' => true,
            'description' => fake()->randomLetter,
            'url' => fake()->randomLetter,
        ];
    }
}
