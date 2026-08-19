<?php

namespace App\Policies;

use App\Enums\Permissions;
use App\Models\Backups\Target;
use App\Models\Users\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class TargetPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): Response|bool
    {
        return $user->hasPermissionTo(Permissions::TARGETS_VIEW_ANY_FOR_USER);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @return Response|bool
     */
    public function view(User $user, Target $target)
    {
        //
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permissions::TARGETS_CREATE);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Target $target): Response|bool
    {
        return $user->hasPermissionTo(Permissions::TARGETS_UPDATE_FOR_USER) && $target->user_id == $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Target $target): bool
    {
        return $user->hasPermissionTo(Permissions::TARGETS_DELETE_FOR_USER) && $target->user_id == $user->id;

    }

    /**
     * Determine whether the user can restore the model.
     *
     * @return Response|bool
     */
    public function restore(User $user, Target $target)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @return Response|bool
     */
    public function forceDelete(User $user, Target $target)
    {
        //
    }
}
