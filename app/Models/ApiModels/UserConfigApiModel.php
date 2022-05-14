<?php

namespace App\Models\ApiModels;

use App\Models\User;
use App\Models\UserConfig;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use JetBrains\PhpStorm\Pure;

class UserConfigApiModel
{
    public bool $sideMenuOpen;

    /**
     * @param UserConfig $entity
     * @return UserConfigApiModel
     */
    #[Pure] static function fromEntity(UserConfig $entity): UserConfigApiModel
    {
        $apiModel = new UserConfigApiModel();

        $apiModel->sideMenuOpen = $entity->side_menu_open;

        return $apiModel;
    }

    /**
     * @param UserConfig[] $entities
     * @return UserConfigApiModel[]
     */
    #[Pure] static function fromEntities($entities): array
    {
        $entries = [];

        foreach ($entities as $entity) {
            $entries[] = self::fromEntity($entity);
        }

        return $entries;
    }
}
