<?php

namespace App\Models\ApiModels\Dashboard;

use App\Models\Dashboard\Folder;
use Carbon\Carbon;

class FolderApiModel
{
    public int $id;
    public Carbon $createdAt;
    public Carbon $updatedAt;
    public string $name;
    public int $sort;
    public bool $show;
    public array $sites;

    /**
     * @param Folder $entity
     * @param bool $withSites
     * @return FolderApiModel
     */
    static function fromEntity(Folder $entity, bool $withSites = true): FolderApiModel
    {
        $apiModel = new FolderApiModel();

        $apiModel->id = $entity->id;
        $apiModel->createdAt = $entity->created_at;
        $apiModel->updatedAt = $entity->updated_at;
        $apiModel->name = $entity->name;
        $apiModel->sort = $entity->sort;
        $apiModel->show = $entity->show;
        $apiModel->sites = $withSites ? SiteApiModel::fromEntities($entity->sites) : [];

        return $apiModel;
    }

    /**
     * @param Folder[] $entities
     * @return FolderApiModel[]
     */
    static function fromEntities($entities): array
    {
        $folders = [];

        foreach ($entities as $entity) {
            $folders[] = self::fromEntity($entity);
        }

        return $folders;
    }
}
