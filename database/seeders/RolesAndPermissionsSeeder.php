<?php

namespace Database\Seeders;

use App\Enums\Permissions;
use App\Enums\Roles;
use App\Models\ApiModels\FamilyMemberStatsApiModel;
use App\Models\ApiModels\RoleApiModel;
use App\Models\Backups\Backup;
use App\Models\Backups\Schedule;
use App\Models\Backups\Target;
use App\Models\Dashboard\Folder;
use App\Models\Dashboard\Site;
use App\Models\Dashboard\SiteImage;
use App\Models\Events\Event;
use App\Models\Events\EventParticipant;
use App\Models\Gaming\GamingDevice;
use App\Models\Gaming\GamingSession;
use App\Models\Goals\Goal;
use App\Models\Goals\GoalDay;
use App\Models\Logging\LogEvent;
use App\Models\Tasks\Family;
use App\Models\Tasks\Tag;
use App\Models\Tasks\Task;
use App\Models\Tasks\TaskUserConfig;
use App\Models\Users\User;
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
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->createAdminRole();
        $this->createGoalsRole();
        $this->createBackupsRole();
        $this->createDashboardRole();
        $this->createTasksRole();
        $this->createEventsRole();
        $this->createFileExplorerRole();
        $this->createMoneyAppRole();
    }

//    private function createDefaultRole(): void
//    {
//        $this->createAndAssign(Roles::DEFAULT_ROLE, [
//            RoleApiModel::viewAnyForUserPermission()
//        ]);
//    }

    private function createFileExplorerRole(): void
    {
        $this->createAndAssign(Roles::GAMING_SESSION_ADMIN_ROLE, [
            GamingSession::updatePermission(),
            GamingSession::deletePermission(),

            GamingDevice::createPermission(),
            GamingDevice::updatePermission(),

            Permissions::VIEW_GAMING_SESSION_ADMIN_PAGE
        ]);
    }

    private function createGamingSessionAdminRole(): void
    {
        $this->createAndAssign(Roles::FILE_EXPLORER_ROLE, [
            Permissions::VIEW_FILE_EXPLORER_PAGE
        ]);
    }

    private function createMoneyAppRole(): void
    {
        $this->createAndAssign(Roles::MONEY_APP_ROLE, [
            Permissions::VIEW_MONEY_APP_PAGE
        ]);
    }

    private function createAdminRole(): void
    {
        $this->createAndAssign(Roles::ADMIN_ROLE, [
            User::viewAnyPermission(),
            User::createPermission(),
            User::updatePermission(),
            User::deletePermission(),

            RoleApiModel::viewAnyPermission(),

            Family::viewAnyPermission(),
            Family::createPermission(),
            Family::updatePermission(),
            Family::deletePermission(),

            LogEvent::viewAnyPermission(),

            Permissions::VIEW_USERS_PAGE,
            Permissions::VIEW_FAMILIES_TAB,
            Permissions::LISTEN_TO_UPTIME_KUMA_WEBSOCKET,
            Permissions::VIEW_LOGGING_PAGE,
        ]);
    }

    /**
     * @param string $role
     * @param string[] $permissions
     * @return void
     */
    private function createAndAssign(string $role, array $permissions): void
    {
        /** @var Role $role */
        $role = Role::findOrCreate($role);
        foreach ($permissions as $permission) {
            /** @var Permission $permission */
            $permission = Permission::findOrCreate($permission);
            $permission->assignRole($role);
        }
    }

    private function createGoalsRole(): void
    {
        $this->createAndAssign(Roles::GOALS_ROLE, [
            Goal::viewAnyForUserPermission(),
            Goal::viewForUserPermission(),
            Goal::createPermission(),
            Goal::updateForUserPermission(),
            Goal::deleteForUserPermission(),
            Goal::restoreForUserPermission(),

            GoalDay::createPermission(),
            GoalDay::updateForUserPermission(),
            GoalDay::deleteForUserPermission(),

            Permissions::VIEW_GOALS_PAGE,
        ]);
    }

    private function createBackupsRole(): void
    {
        $this->createAndAssign(Roles::BACKUPS_ROLE, [
            Backup::viewAnyForUserPermission(),
            Backup::viewForUserPermission(),
            Backup::createPermission(),
            Backup::updateForUserPermission(),

            Schedule::viewAnyForUserPermission(),
            Schedule::viewForUserPermission(),
            Schedule::createPermission(),
            Schedule::updateForUserPermission(),
            Schedule::deleteForUserPermission(),
            Schedule::restoreForUserPermission(),

            Target::viewAnyForUserPermission(),
            Target::createPermission(),
            Target::updateForUserPermission(),
            Target::deleteForUserPermission(),

            Permissions::VIEW_BACKUPS_PAGE,
        ]);
    }

    private function createDashboardRole(): void
    {
        $this->createAndAssign(Roles::DASHBOARD_ROLE, [
            Folder::viewAnyForUserPermission(),
            Folder::createPermission(),
            Folder::updateForUserPermission(),
            Folder::deleteForUserPermission(),

            Site::createPermission(),
            Site::updateForUserPermission(),
            Site::deleteForUserPermission(),

            SiteImage::createPermission(),
            SiteImage::viewForUserPermission(),

            Permissions::VIEW_DASHBOARD_PAGE,
        ]);
    }

    private function createTasksRole(): void
    {
        $this->createAndAssign(Roles::TASK_ROLE, [
            Task::viewAnyForUserPermission(),
            Task::createPermission(),
            Task::viewForUserPermission(),
            Task::updatePermission(),
            Task::deletePermission(),

            Family::viewForUserPermission(),
            Family::updatePermission(),

            FamilyMemberStatsApiModel::viewAnyForUserPermission(),

            TaskUserConfig::viewAnyPermission(),
            TaskUserConfig::updatePermission(),

            Tag::viewAnyForUserPermission(),

            Permissions::VIEW_TASKS_PAGE
        ]);
    }

    private function createEventsRole(): void
    {
        $this->createAndAssign(Roles::EVENT_ROLE, [

            Event::viewAnyForUserPermission(),
            Event::createPermission(),
            Event::updateForUserPermission(),
            Event::deleteForUserPermission(),

            EventParticipant::updatePermission(),
            EventParticipant::deletePermission(),

            Permissions::VIEW_EVENTS_PAGE
        ]);
    }
}
