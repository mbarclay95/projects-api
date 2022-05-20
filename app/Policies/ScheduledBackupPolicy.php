<?php

namespace App\Policies;

use App\Enums\Permissions;
use App\Models\Backups\ScheduledBackup;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class ScheduledBackupPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     * @return Response|bool
     */
    public function viewAny(User $user): Response|bool
    {
        return $user->hasPermissionTo(Permissions::SCHEDULED_BACKUPS_VIEW_ANY_FOR_USER);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param ScheduledBackup $scheduledBackup
     * @return Response|bool
     */
    public function view(User $user, ScheduledBackup $scheduledBackup)
    {
        //
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     * @return Response|bool
     */
    public function create(User $user): Response|bool
    {
        return $user->hasPermissionTo(Permissions::SCHEDULED_BACKUPS_CREATE);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param ScheduledBackup $scheduledBackup
     * @return Response|bool
     */
    public function update(User $user, ScheduledBackup $scheduledBackup): Response|bool
    {
        return $user->hasPermissionTo(Permissions::SCHEDULED_BACKUPS_UPDATE_FOR_USER) &&
            $user->id == $scheduledBackup->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param ScheduledBackup $scheduledBackup
     * @return Response|bool
     */
    public function delete(User $user, ScheduledBackup $scheduledBackup): Response|bool
    {
        return $user->hasPermissionTo(Permissions::SCHEDULED_BACKUPS_DELETE_FOR_USER) &&
            $user->id == $scheduledBackup->user_id;

    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param ScheduledBackup $scheduledBackup
     * @return Response|bool
     */
    public function restore(User $user, ScheduledBackup $scheduledBackup)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param ScheduledBackup $scheduledBackup
     * @return Response|bool
     */
    public function forceDelete(User $user, ScheduledBackup $scheduledBackup)
    {
        //
    }
}
