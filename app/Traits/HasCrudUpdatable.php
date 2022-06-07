<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

trait HasCrudUpdatable
{
    public static function updateEntity(Model $entity, $request)
    {

    }

    public static function updateUserEntity(Model $entity, $request, User $auth)
    {

    }
}
