<?php

namespace App\Models\ApiModels\Dashboard;

use App\Models\Dashboard\SiteImage;
use Carbon\Carbon;

class SiteImageApiModel
{
    public int $id;
    public Carbon $createdAt;
    public Carbon $updatedAt;
    public string $originalFileName;
    public string $s3Path;

    /**
     * @param SiteImage $entity
     * @return SiteImageApiModel
     */
    static function fromEntity(SiteImage $entity): SiteImageApiModel
    {
        $apiModel = new SiteImageApiModel();

        $apiModel->id = $entity->id;
        $apiModel->createdAt = $entity->created_at;
        $apiModel->updatedAt = $entity->updated_at;
        $apiModel->originalFileName = $entity->original_file_name;
        $apiModel->s3Path = $entity->s3_path;

        return $apiModel;
    }

    /**
     * @param SiteImage[] $entities
     * @return SiteImageApiModel[]
     */
    static function fromEntities($entities): array
    {
        $siteImages = [];

        foreach ($entities as $entity) {
            $siteImages[] = self::fromEntity($entity);
        }

        return $siteImages;
    }
}
