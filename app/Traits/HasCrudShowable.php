<?php

namespace App\Traits;

trait HasCrudShowable
{
    public static function getEntity(int $entityId)
    {
        return static::class::query()
                            ->where('id', '=', $entityId)
                            ->first();
    }

    public static function getUserEntity(int $entityId, int $authId)
    {
        return static::class::query()
                            ->where('user_id', '=', $authId)
                            ->where('id', '=', $entityId)
                            ->first();
    }
}
