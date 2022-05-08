<?php

namespace App\Models\ApiModels\Backups;

use App\Models\Backups\BackupStep;
use Carbon\Carbon;
use JetBrains\PhpStorm\Pure;

class BackupStepApiModel
{
    public int $id;
    public string $name;
    public Carbon|null $startedAt;
    public Carbon|null $completedAt;
    public Carbon|null $erroredAt;
    public bool $fullBackup;
    public string $sourceDir;
    public int $sort;
    public TargetApiModel $target;

    /**
     * @param BackupStep[] $entities
     * @return BackupStepApiModel[]
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
     * @param BackupStep $entity
     * @return BackupStepApiModel
     */
    #[Pure] static function fromEntity(BackupStep $entity): BackupStepApiModel
    {
        $apiModel = new BackupStepApiModel();

        $apiModel->id = $entity->id;
        $apiModel->name = $entity->name;
        $apiModel->startedAt = $entity->started_at;
        $apiModel->completedAt = $entity->completed_at;
        $apiModel->erroredAt = $entity->errored_at;
        $apiModel->fullBackup = $entity->full_backup;
        $apiModel->sourceDir = $entity->source_dir;
        $apiModel->sort = $entity->sort;
        $apiModel->target = TargetApiModel::fromEntity($entity->target);

        return $apiModel;
    }
}
