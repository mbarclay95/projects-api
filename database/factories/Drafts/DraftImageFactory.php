<?php

namespace Database\Factories\Drafts;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Drafts\DraftImage>
 */
class DraftImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            's3_path' => 'draft-images/' . fake()->uuid . '.png',
            'original_file_name' => 'logo.png',
        ];
    }
}
