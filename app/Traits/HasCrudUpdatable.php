<?php

namespace App\Traits;

use App\Models\Users\User;
use Illuminate\Database\Eloquent\Model;

trait HasCrudUpdatable
{
    public static function updateEntity(Model $entity, $request, User $auth)
    {

    }
}
