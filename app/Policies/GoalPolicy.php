<?php

namespace App\Policies;

use App\Enums\Permissions;
use App\Models\Goals\Goal;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class GoalPolicy
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
        return $user->hasPermissionTo(Permissions::GOALS_VIEW_ANY_FOR_USER);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param Goal $goal
     * @return Response|bool
     */
    public function view(User $user, Goal $goal): Response|bool
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
        return $user->hasPermissionTo(Permissions::GOALS_CREATE);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Goal $goal
     * @return Response|bool
     */
    public function update(User $user, Goal $goal): Response|bool
    {
        return $user->hasPermissionTo(Permissions::GOALS_UPDATE_FOR_USER) && $user->id == $goal->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Goal $goal
     * @return Response|bool
     */
    public function delete(User $user, Goal $goal): Response|bool
    {
        return $user->hasPermissionTo(Permissions::GOALS_DELETE_FOR_USER) && $user->id == $goal->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param Goal $goal
     * @return Response|bool
     */
    public function restore(User $user, Goal $goal): Response|bool
    {
        return $user->hasPermissionTo(Permissions::GOALS_RESTORE_FOR_USER) && $user->id == $goal->user_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param Goal $goal
     * @return Response|bool
     */
    public function forceDelete(User $user, Goal $goal): Response|bool
    {
        //
    }
}
