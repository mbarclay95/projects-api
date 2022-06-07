<?php

namespace App\Traits;

use App\Models\User;

trait HasCrudStorable
{
    public static function createEntity($request, User $auth)
    {
//        $entity = new static::class;
//        return $entity;
    }
}
