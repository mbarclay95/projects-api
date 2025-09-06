<?php

namespace App\Repositories\Users;

use App\Enums\Roles;
use App\Models\ApiModels\RoleApiModel;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Mbarclay36\LaravelCrud\DefaultRepository;
use Spatie\Permission\Models\Role;

class RolesRepository extends DefaultRepository
{
    /**
     * @param $request
     * @param Authenticatable $user
     * @param bool $viewOnlyForUser
     * @return Collection|Role[]
     */
    public function getEntities($request, Authenticatable $user, bool $viewOnlyForUser): Collection|array
    {
        return Role::query()
            ->when(!$user->hasPermissionTo(RoleApiModel::viewAnyPermission()), function ($where) {
                $where->where('name', '!=', Roles::ADMIN_ROLE);
            })
            ->orderBy('name')
            ->get();
    }

    protected static function getModelClass(): string
    {
        return Role::class;
    }
}
