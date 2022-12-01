<?php

namespace Database\Seeders;

use App\Enums\Roles;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $user = new User([
            'name' => 'Michael Barclay',
            'username' => 'mbarclay36',
            'password' => Hash::make('1234'),
        ]);
        $user->save();
        $role = Role::findByName(Roles::USERS_ROLE);
        $user->syncRoles([$role]);
    }
}
