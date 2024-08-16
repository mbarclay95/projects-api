<?php

namespace App\Services\Backups\BackupStepTypes;

use App\Models\Backups\BackupStep;
use App\Models\Backups\Target;
use Exception;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

class S3UploadBackupStepType extends DefaultBackupStepType
{
    static string $BACKUP_STEP_TYPE = 's3_upload';
    private Target $source_target;
    private Target $destination_target;
    private string $s3_driver;
    private string $file_name;

    public function validateAndSetConfig(array $config): bool
    {
        if (!$config['sourceTargetId']) {
            return false;
        }
        /** @var Target $sourceTarget */
        $sourceTarget = Target::query()->find($config['sourceTargetId']);
        if (!$sourceTarget) {
            return false;
        }
        $this->source_target = $sourceTarget;

        if (!$config['destinationTargetId']) {
            return false;
        }
        /** @var Target $destinationTarget */
        $destinationTarget= Target::query()->find($config['destinationTargetId']);
        if (!$destinationTarget) {
            return false;
        }
        $this->destination_target = $destinationTarget;
        if (!($config['s3Driver'] === 'minio-s3' || $config['s3Driver'] === 'aws-s3')) {
            return false;
        }
        $this->s3_driver = $config['s3Driver'];
        if (!$config['fileName']) {
            return false;
        }
        $this->file_name = $config['fileName'];

        return true;
    }

    /**
     * @throws Exception
     */
    public function runStep(): void
    {
        $destination = $this->destination_target->target_url;
        if (str_ends_with($destination, '/')) {
            $destination = substr($destination, 0, -1);
        }
        $source = $this->source_target->target_url;
        if (!str_ends_with($source, '/')) {
            $source = $source . '/';
        }
        $file = new File("{$source}{$this->file_name}");
        Storage::disk($this->s3_driver)->putFileAs($destination, $file, $this->file_name);
    }
}
