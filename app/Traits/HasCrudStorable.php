<?php

namespace App\Traits;

trait HasCrudStorable
{
    public static function createEntity($request, int $authId)
    {
        $entity = new static::class();
        return $entity;
    }
}
