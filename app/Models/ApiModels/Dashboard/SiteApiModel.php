<?php

namespace App\Models\ApiModels\Dashboard;

use App\Models\Dashboard\Site;
use App\Models\Dashboard\SiteImage;
use Carbon\Carbon;

class SiteApiModel
{
    public int $id;
    public Carbon $createdAt;
    public Carbon $updatedAt;
    public string $name;
    public string $description;
    public int $sort;
    public bool $show;
    public string $url;
    public int $folderId;
    public SiteImageApiModel|null $siteImage;

    /**
     * @param Site $entity
     * @return SiteApiModel
     */
    static function fromEntity(Site $entity): SiteApiModel
    {
        $apiModel = new SiteApiModel();

        $apiModel->id = $entity->id;
        $apiModel->createdAt = $entity->created_at;
        $apiModel->updatedAt = $entity->updated_at;
        $apiModel->name = $entity->name;
        $apiModel->description = $entity->description;
        $apiModel->sort = $entity->sort;
        $apiModel->show = $entity->show;
        $apiModel->url = $entity->url;
        $apiModel->siteImage = $entity->siteImage ? SiteImageApiModel::fromEntity($entity->siteImage) : null;
        $apiModel->folderId = $entity->folder_id;

        return $apiModel;
    }

    /**
     * @param Site[] $entities
     * @return SiteApiModel[]
     */
    static function fromEntities($entities): array
    {
        $sites = [];

        foreach ($entities as $entity) {
            $sites[] = self::fromEntity($entity);
        }

        return $sites;
    }
}
