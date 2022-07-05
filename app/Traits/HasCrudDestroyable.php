<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

trait HasCrudDestroyable
{
    public static function destroyEntity(Model $entity): void
    {
        $entity->delete();
    }

    public static function destroyUserEntity(Model $entity, User $auth): void
    {
        $entity->delete();
    }
}
