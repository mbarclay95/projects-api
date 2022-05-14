<?php

namespace App\Policies;

use App\Enums\Permissions;
use App\Models\Dashboard\SiteImage;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class SiteImagePolicy
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
     * @param SiteImage $siteImage
     * @return Response|bool
     */
    public function view(User $user, SiteImage $siteImage): Response|bool
    {
        return $user->hasPermissionTo(Permissions::SITE_IMAGES_VIEW_FOR_USER) && $siteImage->user_id == $user->id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     * @return Response|bool
     */
    public function create(User $user): Response|bool
    {
        return $user->hasPermissionTo(Permissions::SITE_IMAGES_CREATE);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param SiteImage $siteImage
     * @return Response|bool
     */
    public function update(User $user, SiteImage $siteImage)
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param SiteImage $siteImage
     * @return Response|bool
     */
    public function delete(User $user, SiteImage $siteImage)
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param SiteImage $siteImage
     * @return Response|bool
     */
    public function restore(User $user, SiteImage $siteImage)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param SiteImage $siteImage
     * @return Response|bool
     */
    public function forceDelete(User $user, SiteImage $siteImage)
    {
        //
    }
}
