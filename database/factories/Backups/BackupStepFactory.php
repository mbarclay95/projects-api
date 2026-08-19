<?php

namespace Database\Factories\Backups;

use App\Models\Backups\BackupStep;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BackupStep>
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
            'config' => [],
        ];
    }
}
