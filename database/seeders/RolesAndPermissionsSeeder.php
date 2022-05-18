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
        $this->createBackupsRole();
        $this->createDashboardRole();
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

    private function createBackupsRole()
    {
        $this->createAndAssign(Roles::BACKUPS_ROLE, [
            Permissions::BACKUPS_VIEW_ANY_FOR_USER,
            Permissions::BACKUPS_VIEW_FOR_USER,
            Permissions::BACKUPS_CREATE,
            Permissions::BACKUPS_UPDATE_FOR_USER,
            Permissions::BACKUPS_RUN_ACTIONS,

            Permissions::SCHEDULED_BACKUPS_VIEW_ANY_FOR_USER,
            Permissions::SCHEDULED_BACKUPS_VIEW_FOR_USER,
            Permissions::SCHEDULED_BACKUPS_CREATE,
            Permissions::SCHEDULED_BACKUPS_UPDATE_FOR_USER,
            Permissions::SCHEDULED_BACKUPS_DELETE_FOR_USER,
            Permissions::SCHEDULED_BACKUPS_RESTORE_FOR_USER,

            Permissions::TARGETS_VIEW_ANY_FOR_USER,
            Permissions::TARGETS_CREATE,
            Permissions::TARGETS_UPDATE_FOR_USER,
            Permissions::TARGETS_DELETE_FOR_USER,

            Permissions::VIEW_BACKUPS_PAGE,
        ]);
    }

    private function createDashboardRole()
    {
        $this->createAndAssign(Roles::DASHBOARD_ROLE, [
            Permissions::FOLDERS_VIEW_ANY_FOR_USER,
            Permissions::FOLDERS_CREATE,
            Permissions::FOLDERS_UPDATE_FOR_USER,
            Permissions::FOLDERS_DELETE_FOR_USER,

            Permissions::SITES_CREATE,
            Permissions::SITES_UPDATE_FOR_USER,
            Permissions::SITES_DELETE_FOR_USER,

            Permissions::SITE_IMAGES_CREATE,
            Permissions::SITE_IMAGES_VIEW_FOR_USER,

            Permissions::VIEW_DASHBOARD_PAGE,
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
