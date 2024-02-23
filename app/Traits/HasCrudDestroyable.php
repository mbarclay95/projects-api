<?php

namespace App\Traits;

use App\Models\Users\User;
use Illuminate\Database\Eloquent\Model;

trait HasCrudDestroyable
{
    public static function destroyEntity(Model $entity, User $auth): void
    {
        $entity->delete();
    }
}
