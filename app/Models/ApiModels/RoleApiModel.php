<?php

namespace App\Models\ApiModels;

use App\Models\User;
use App\Traits\HasApiModel;
use App\Traits\HasCrudIndexable;
use App\Traits\HasCrudPermissions;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Role;


class RoleApiModel
{
    use HasApiModel, HasCrudIndexable, HasCrudPermissions;

    protected static array $apiModelAttributes = ['id', 'name'];

    public static function getEntities($request, User $auth, bool $viewAnyForUser): Collection|array
    {
        return Role::query()->get();
    }
}
