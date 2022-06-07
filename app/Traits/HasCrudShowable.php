<?php

namespace App\Traits;

use App\Models\User;

trait HasCrudShowable
{
    public static function getEntity(int $entityId)
    {
        return static::class::query()
                            ->where('id', '=', $entityId)
                            ->first();
    }

    public static function getUserEntity(int $entityId, User $auth)
    {
        return static::class::query()
                            ->where('user_id', '=', $auth->id)
                            ->where('id', '=', $entityId)
                            ->first();
    }
}
