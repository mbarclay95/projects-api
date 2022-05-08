<?php

namespace App\Models\ApiModels\Backups;

use App\Models\Backups\Backup;
use App\Models\Backups\Target;
use Carbon\Carbon;
use JetBrains\PhpStorm\Pure;

class TargetApiModel
{
    public int $id;
    public string $name;
    public string $targetUrl;
    public string $hostName;

    /**
     * @param Target[] $entities
     * @return TargetApiModel[]
     */
    #[Pure] static function fromEntities($entities): array
    {
        $apiModels = [];

        foreach ($entities as $entity) {
            $apiModels[] = self::fromEntity($entity);
        }

        return $apiModels;
    }

    /**
     * @param Target $entity
     * @return TargetApiModel
     */
    #[Pure] static function fromEntity(Target $entity): TargetApiModel
    {
        $apiModel = new TargetApiModel();

        $apiModel->id = $entity->id;
        $apiModel->name = $entity->name;
        $apiModel->targetUrl = $entity->target_url;
        $apiModel->hostName = $entity->host_name;


        return $apiModel;
    }
}
