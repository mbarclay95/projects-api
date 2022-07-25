<?php

namespace App\Traits;

use App\Models\User;

trait HasCrudIndexable
{
    public static function getEntities($request)
    {
        return static::class::query()
                            ->orderBy('id')
                            ->get();
    }

    public static function getUserEntities($request, User $auth)
    {
        return static::class::query()
                            ->where('user_id', '=', $auth->id)
                            ->orderBy('id')
                            ->get();
    }
}
