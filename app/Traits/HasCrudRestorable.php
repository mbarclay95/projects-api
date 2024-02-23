<?php

namespace App\Traits;

use App\Models\Users\User;

trait HasCrudRestorable
{
    public static function createEntity($request, User $auth)
    {
        $entity = new static::class();
        return $entity;
    }
}
