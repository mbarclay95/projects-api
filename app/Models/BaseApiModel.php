<?php

namespace App\Models;

use App\Enums\Permissions;
use App\Traits\HasApiModel;
use App\Traits\HasCrudDestroyable;
use App\Traits\HasCrudIndexable;
use App\Traits\HasCrudShowable;
use App\Traits\HasCrudStorable;
use App\Traits\HasCrudUpdatable;
use Illuminate\Database\Eloquent\Model;

class BaseApiModel extends Model
{
    use HasApiModel, HasCrudIndexable, HasCrudStorable, HasCrudShowable, HasCrudUpdatable, HasCrudDestroyable;

    protected static string $viewAnyPermission;
    protected static string $viewAnyForUserPermission;
    protected static string $viewPermission;
    protected static string $viewForUserPermission;
    protected static string $createPermission;
    protected static string $updatePermission;
    protected static string $updateForUserPermission;
    protected static string $deletePermission;
    protected static string $deleteForUserPermission;
    protected static string $restorePermission;
    protected static string $restoreForUserPermission;

    protected static $unguarded = true;

    public function __construct(array $attributes = [])
    {
        static::$viewAnyPermission = static::class . Permissions::VIEW_ANY;
        static::$viewAnyForUserPermission = static::class . Permissions::VIEW_ANY_FOR_USER;
        static::$viewPermission = static::class . Permissions::VIEW;
        static::$viewForUserPermission = static::class . Permissions::VIEW_FOR_USER;
        static::$createPermission = static::class . Permissions::CREATE;
        static::$updatePermission = static::class . Permissions::UPDATE;
        static::$updateForUserPermission = static::class . Permissions::UPDATE_FOR_USER;
        static::$deletePermission = static::class . Permissions::DELETE;
        static::$deleteForUserPermission = static::class . Permissions::DELETE_FOR_USER;
        static::$restorePermission = static::class . Permissions::RESTORE;
        static::$restoreForUserPermission = static::class . Permissions::RESTORE_FOR_USER;

        parent::__construct($attributes);
    }
}
