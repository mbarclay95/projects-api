<?php

namespace App\Traits;

trait HasCrudIndexable
{
    public static function getEntities($request)
    {
        return static::class::query()
                            ->get();
    }

    public static function getUserEntities($request, int $authId)
    {
        return static::class::query()
                            ->where('user_id', '=', $authId)
                            ->get();
    }
}
