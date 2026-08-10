<?php

namespace App\Repositories\Drafts;

use App\Enums\Roles;
use App\Models\Users\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Mbarclay36\LaravelCrud\DefaultRepository;

class DraftAdminCandidatesRepository extends DefaultRepository
{
    /**
     * id and name only, on purpose — see admin-crud.md. This endpoint fills
     * one dropdown and should teach a draft admin nothing about roles,
     * usernames, or config.
     *
     * @param $request
     * @param Authenticatable $user
     * @param bool $viewOnlyForUser
     * @return Collection|User[]
     */
    public function getEntities($request, Authenticatable $user, bool $viewOnlyForUser): Collection|array
    {
        return User::query()
                   ->role(Roles::DRAFTS_ROLE)
                   ->orderBy('name')
                   ->get();
    }
}
