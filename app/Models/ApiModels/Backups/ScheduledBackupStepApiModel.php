<?php

namespace App\Models\ApiModels\Backups;

use App\Models\Backups\BackupStep;
use App\Models\Backups\ScheduledBackupStep;
use Carbon\Carbon;
use JetBrains\PhpStorm\Pure;

class ScheduledBackupStepApiModel
{
    public int $id;
    public string $name;
    public string $sourceDir;
    public int $sort;
    public TargetApiModel $target;

    /**
     * @param ScheduledBackupStep[] $entities
     * @return ScheduledBackupApiModel[]
     */
    #[Pure] static function fromEntities($entities): array
    {
        $backupSteps = [];

        foreach ($entities as $entity) {
            $backupSteps[] = self::fromEntity($entity);
        }

        return $backupSteps;
    }

    /**
     * @param ScheduledBackupStep $entity
     * @return ScheduledBackupStepApiModel
     */
    #[Pure] static function fromEntity(ScheduledBackupStep $entity): ScheduledBackupStepApiModel
    {
        $apiModel = new ScheduledBackupStepApiModel();

        $apiModel->id = $entity->id;
        $apiModel->name = $entity->name;
        $apiModel->sourceDir = $entity->source_dir;
        $apiModel->sort = $entity->sort;
        $apiModel->target = TargetApiModel::fromEntity($entity->target);

        return $apiModel;
    }
}
