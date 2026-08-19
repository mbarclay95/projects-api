<?php

namespace Database\Factories\Backups;

use App\Models\Backups\Backup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Backup>
 */
class BackupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'testing backup',
        ];
    }
}
