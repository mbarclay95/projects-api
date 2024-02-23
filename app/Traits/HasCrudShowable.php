<?php

namespace App\Traits;

use App\Models\Users\User;

trait HasCrudShowable
{
    public static function getEntity(int $entityId, User $auth, bool $viewForUser)
    {
        return static::class::query()
                            ->where('id', '=', $entityId)
                            ->first();
    }
}
