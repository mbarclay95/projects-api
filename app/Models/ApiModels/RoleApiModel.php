<?php

namespace App\Models\ApiModels;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use JetBrains\PhpStorm\Pure;
use Spatie\Permission\Models\Role;

class RoleApiModel
{
    public int $id;
    public string $name;

    /**
     * @param Role $entity
     * @return RoleApiModel
     */
    #[Pure] static function fromEntity(Role $entity): RoleApiModel
    {
        $apiModel = new RoleApiModel();

        $apiModel->id = $entity->id;
        $apiModel->name = $entity->name;

        return $apiModel;
    }

    /**
     * @param Role[] $entities
     * @return RoleApiModel[]
     */
    #[Pure] static function fromEntities($entities): array
    {
        $roles = [];

        foreach ($entities as $entity) {
            $roles[] = self::fromEntity($entity);
        }

        return $roles;
    }
}
