<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class DefaultRepository implements RepositoryInterface
{
    public function getEntities($request, User $user): Collection|array
    {
        return [];
    }

    public function getEntity(int $id, User $user): Model|array
    {
        return [];
    }

    public function createEntity($request, User $user): Model|array
    {
        return [];
    }

    public function updateEntity(Model $model, $request, User $user): Model|array
    {
        return [];
    }

    public function destroyEntity(Model $model, User $user): bool
    {
        $model->delete();

        return true;
    }

    public static function getEntitiesStatic($request, User $user): Collection|array
    {
        return (new static())->getEntities($request, $user);
    }

    public static function getEntityStatic(int $id, User $user): Model|array
    {
        return (new static())->getEntity($id, $user);
    }

    public static function createEntityStatic($request, User $user): Model|array
    {
        return (new static())->createEntity($request, $user);
    }

    public static function updateEntityStatic(Model $model, $request, User $user): Model|array
    {
        return (new static())->updateEntity($model, $request, $user);
    }

    public static function destroyEntityStatic(Model $model, User $user): bool
    {
        return (new static())->destroyEntity($model, $user);
    }
}
