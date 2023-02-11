<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface RepositoryInterface
{
    public function getEntities($request, User $user): Collection|array;
    public static function getEntitiesStatic($request, User $user): Collection|array;
    public function getEntity(int $id, User $user): Model|array;
    public static function getEntityStatic(int $id, User $user): Model|array;
    public function createEntity($request, User $user): Model|array;
    public static function createEntityStatic($request, User $user): Model|array;
    public function updateEntity(Model $model, $request, User $user): Model|array;
    public static function updateEntityStatic(Model $model, $request, User $user): Model|array;
    public function destroyEntity(Model $model, User $user): bool;
    public static function destroyEntityStatic(Model $model, User $user): bool;
}
