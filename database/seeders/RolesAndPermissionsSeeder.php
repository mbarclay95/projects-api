<?php

namespace Database\Seeders;

use App\Enums\Permissions;
use App\Enums\Roles;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->createUsersRole();
        $this->createGoalsRole();
    }

    private function createUsersRole()
    {
        $this->createAndAssign(Roles::USERS_ROLE, [
            Permissions::USERS_VIEW_ANY,
            Permissions::USERS_CREATE,
            Permissions::USERS_UPDATE,
            Permissions::USERS_DELETE,

            Permissions::VIEW_USERS_PAGE,
        ]);
    }

    private function createGoalsRole()
    {
        $this->createAndAssign(Roles::GOALS_ROLE, [
            Permissions::GOALS_VIEW_ANY_FOR_USER,
            Permissions::GOALS_VIEW_FOR_USER,
            Permissions::GOALS_CREATE,
            Permissions::GOALS_UPDATE_FOR_USER,
            Permissions::GOALS_DELETE_FOR_USER,
            Permissions::GOALS_RESTORE_FOR_USER,

            Permissions::VIEW_GOALS_PAGE,
        ]);
    }

    /**
     * @param string $role
     * @param string[] $permissions
     * @return void
     */
    private function createAndAssign(string $role, array $permissions)
    {
        /** @var Role $role */
        $role = Role::findOrCreate($role);
        foreach ($permissions as $permission) {
            /** @var Permission $permission */
            $permission = Permission::findOrCreate($permission);
            $permission->assignRole($role);
        }
    }
}
