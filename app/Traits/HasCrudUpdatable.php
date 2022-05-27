<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;

trait HasCrudUpdatable
{
    public static function updateEntity(Model $entity, $request)
    {

    }

    public static function updateUserEntity(Model $entity, $request, int $authId)
    {

    }
}
