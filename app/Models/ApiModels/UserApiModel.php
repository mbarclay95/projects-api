<?php

namespace App\Models\ApiModels;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use JetBrains\PhpStorm\Pure;

class UserApiModel
{
    public int $id;
    public Carbon $createdAt;
    public Carbon|null $lastLoggedInAt;
    public string $name;
    public Collection $permissions;

    /**
     * @param User $entity
     * @return UserApiModel
     */
    #[Pure] static function fromMeEntity(User $entity): UserApiModel
    {
        $apiModel = new UserApiModel();

        $apiModel->id = $entity->id;
        $apiModel->createdAt = $entity->created_at;
        $apiModel->lastLoggedInAt = $entity->last_logged_in_at;
        $apiModel->name = $entity->name;
        $apiModel->permissions = $entity->getAllPermissions()
                                        ->filter(function ($value) {
                                            return str_contains($value, 'client_');
                                        })
                                        ->pluck('name');

        return $apiModel;
    }

    /**
     * @param User[] $entities
     * @return UserApiModel[]
     */
    #[Pure] static function fromEntities($entities): array
    {
        $entries = [];

        foreach ($entities as $entity) {
            $entries[] = self::fromEntity($entity);
        }

        return $entries;
    }

    /**
     * @param User $entity
     * @return UserApiModel
     */
    #[Pure] static function fromEntity(User $entity): UserApiModel
    {
        $apiModel = new UserApiModel();

        $apiModel->id = $entity->id;
        $apiModel->createdAt = $entity->created_at;
        $apiModel->lastLoggedInAt = $entity->last_logged_in_at;
        $apiModel->name = $entity->name;

        return $apiModel;
    }
}
