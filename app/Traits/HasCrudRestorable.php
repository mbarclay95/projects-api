<?php

namespace App\Traits;

trait HasCrudRestorable
{
    public static function createEntity($request, int $authId)
    {
        $entity = new static::class();
        return $entity;
    }
}
