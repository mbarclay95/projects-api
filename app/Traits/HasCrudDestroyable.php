<?php

namespace App\Traits;

use App\Models\User;

trait HasCrudDestroyable
{
    public static function destroyEntity(int $entityId)
    {
        return static::class::query()
                            ->where('id', '=', $entityId)
                            ->delete();
    }

    public static function destroyUserEntity(int $entityId, User $auth)
    {
        return static::class::query()
                            ->where('id', '=', $entityId)
                            ->where('user_id', '=', $auth->id)
                            ->delete();
    }
}
