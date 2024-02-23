<?php

namespace App\Traits;

use App\Models\Users\User;

trait HasCrudIndexable
{
    public static function getEntities($request, User $auth, bool $viewAnyForUser)
    {
        return static::class::query()
                            ->orderBy('id')
                            ->get();
    }
}
