<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

trait HasCrudDestroyable
{
    public static function destroyEntity(Model $entity, User $auth): void
    {
        $entity->delete();
    }
}
