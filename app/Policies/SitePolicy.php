<?php

namespace App\Policies;

use App\Enums\Permissions;
use App\Models\Dashboard\Site;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class SitePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     * @return Response|bool
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param Site $site
     * @return Response|bool
     */
    public function view(User $user, Site $site)
    {
        //
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permissions::SITES_CREATE);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Site $site
     * @return Response|bool
     */
    public function update(User $user, Site $site): Response|bool
    {
        return $user->hasPermissionTo(Permissions::SITES_UPDATE_FOR_USER) && $site->user_id == $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Site $site
     * @return bool
     */
    public function delete(User $user, Site $site): bool
    {
        return $user->hasPermissionTo(Permissions::SITES_DELETE_FOR_USER) && $site->user_id == $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param Site $site
     * @return Response|bool
     */
    public function restore(User $user, Site $site)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param Site $site
     * @return Response|bool
     */
    public function forceDelete(User $user, Site $site)
    {
        //
    }
}
