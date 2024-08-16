<?php

namespace Database\Factories\Backups;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Backups\BackupStep>
 */
class BackupStepFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'testing step',
            'sort' => 1,
            'config' => []
        ];
    }
}
