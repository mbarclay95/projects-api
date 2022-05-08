<?php

namespace App\Models\ApiModels\Backups;

use App\Models\Backups\Backup;
use Carbon\Carbon;
use JetBrains\PhpStorm\Pure;

class BackupApiModel
{
    public int $id;
    public string $name;
    public Carbon|null $startedAt;
    public Carbon|null $completedAt;
    public Carbon|null $erroredAt;
    public int|null $scheduledBackupId;
    public array $backupSteps;

    /**
     * @param Backup[] $entities
     * @return BackupApiModel[]
     */
    #[Pure] static function fromEntities($entities): array
    {
        $backups = [];

        foreach ($entities as $entity) {
            $backups[] = self::fromEntity($entity);
        }

        return $backups;
    }

    /**
     * @param Backup $entity
     * @return BackupApiModel
     */
    #[Pure] static function fromEntity(Backup $entity): BackupApiModel
    {
        $apiModel = new BackupApiModel();

        $apiModel->id = $entity->id;
        $apiModel->name = $entity->name;
        $apiModel->startedAt = $entity->started_at;
        $apiModel->completedAt = $entity->completed_at;
        $apiModel->erroredAt = $entity->errored_at;
        $apiModel->scheduledBackupId = $entity->scheduled_backup_id;
        $apiModel->backupSteps = BackupStepApiModel::fromEntities($entity->backupSteps);

        return $apiModel;
    }
}
