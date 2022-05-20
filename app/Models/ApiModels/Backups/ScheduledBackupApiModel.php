<?php

namespace App\Models\ApiModels\Backups;

use App\Models\Backups\Backup;
use App\Models\Backups\ScheduledBackup;
use Carbon\Carbon;
use JetBrains\PhpStorm\Pure;

class ScheduledBackupApiModel
{
    public int $id;
    public string $name;
    public int $startTime;
    public array $schedule;
    public int $fullEveryNDays;
    public bool $enabled;
    public array $scheduleBackupSteps;

    /**
     * @param ScheduledBackup[] $entities
     * @return ScheduledBackupApiModel[]
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
     * @param ScheduledBackup $entity
     * @return ScheduledBackupApiModel
     */
    #[Pure] static function fromEntity(ScheduledBackup $entity): ScheduledBackupApiModel
    {
        $apiModel = new ScheduledBackupApiModel();

        $apiModel->id = $entity->id;
        $apiModel->name = $entity->name;
        $apiModel->startTime = $entity->start_time;
        $apiModel->schedule = $entity->schedule;
        $apiModel->fullEveryNDays = $entity->full_every_n_days;
        $apiModel->enabled = $entity->enabled;
        $apiModel->scheduleBackupSteps = ScheduledBackupStepApiModel::fromEntities($entity->scheduledBackupSteps);

        return $apiModel;
    }
}
