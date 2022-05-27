<?php

namespace App\Traits;

trait HasCrudDestroyable
{
    public static function destroyEntity(int $entityId)
    {
        return static::class::query()
                            ->where('id', '=', $entityId)
                            ->delete();
    }

    public static function destroyUserEntity(int $entityId, int $authId)
    {
        return static::class::query()
                            ->where('id', '=', $entityId)
                            ->where('user_id', '=', $authId)
                            ->delete();
    }
}
