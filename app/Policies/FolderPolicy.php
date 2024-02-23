<?php

namespace App\Policies;

use App\Enums\Permissions;
use App\Models\Dashboard\Folder;
use App\Models\Users\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class FolderPolicy
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
        return $user->hasPermissionTo(Permissions::FOLDERS_VIEW_ANY_FOR_USER);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param Folder $folder
     * @return Response|bool
     */
    public function view(User $user, Folder $folder)
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
        return $user->hasPermissionTo(Permissions::FOLDERS_CREATE);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Folder $folder
     * @return Response|bool
     */
    public function update(User $user, Folder $folder): Response|bool
    {
        return $user->hasPermissionTo(Permissions::FOLDERS_UPDATE_FOR_USER) && $folder->user_id == $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Folder $folder
     * @return Response|bool
     */
    public function delete(User $user, Folder $folder): Response|bool
    {
        return $user->hasPermissionTo(Permissions::FOLDERS_DELETE_FOR_USER) && $folder->user_id == $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param Folder $folder
     * @return Response|bool
     */
    public function restore(User $user, Folder $folder)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param Folder $folder
     * @return Response|bool
     */
    public function forceDelete(User $user, Folder $folder)
    {
        //
    }
}
