<?php

namespace App\Services\Backups\BackupStepTypes;

use App\Models\Backups\BackupStep;
use Exception;

class DefaultBackupStepType implements BackupStepTypeInterface
{
    static string $BACKUP_STEP_TYPE = '';
    public BackupStep $backupStep;

    /**
     * @param BackupStep $backupStep
     * @return void
     * @throws Exception
     */
    public function __construct(BackupStep $backupStep)
    {
        $this->backupStep = $backupStep;
        if (!$this->validateAndSetConfig($this->backupStep->config)) {
            throw new Exception('Config is not set up correctly for backupStepId:' . $this->backupStep->id);
        }
    }

    /**
     * @throws Exception
     */
    static public function getBackupStepTypeClass(BackupStep $backupStep): BackupStepTypeInterface
    {
        return match ($backupStep->backup_step_type) {
            TarZipBackupStepType::$BACKUP_STEP_TYPE => new TarZipBackupStepType($backupStep),
            S3UploadBackupStepType::$BACKUP_STEP_TYPE => new S3UploadBackupStepType($backupStep),
            default => throw new Exception('Invalid backupStepType for backupStepId: ' . $backupStep->id),
        };
    }

    public function runStep(): void
    {
    }

    function validateAndSetConfig(array $config): bool
    {
        return false;
    }
}
