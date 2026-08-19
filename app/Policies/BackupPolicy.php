<?php

namespace App\Policies;

use App\Enums\Permissions;
use App\Models\Backups\Backup;
use App\Models\Users\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class BackupPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): Response|bool
    {
        return $user->hasPermissionTo(Permissions::BACKUPS_VIEW_ANY_FOR_USER);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Backup $backup): Response|bool
    {
        return $user->hasPermissionTo(Permissions::BACKUPS_VIEW_FOR_USER) && $backup->user_id == $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response|bool
    {
        return $user->hasPermissionTo(Permissions::BACKUPS_CREATE);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Backup $backup): Response|bool
    {
        return $user->hasPermissionTo(Permissions::BACKUPS_UPDATE_FOR_USER) && $backup->user_id == $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @return Response|bool
     */
    public function delete(User $user, Backup $backup)
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @return Response|bool
     */
    public function restore(User $user, Backup $backup)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @return Response|bool
     */
    public function forceDelete(User $user, Backup $backup)
    {
        //
    }
}
