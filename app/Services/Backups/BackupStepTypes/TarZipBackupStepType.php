<?php

namespace App\Services\Backups\BackupStepTypes;

use App\Models\Backups\BackupStep;
use App\Models\Backups\Target;
use Exception;

class TarZipBackupStepType extends DefaultBackupStepType
{
    static string $BACKUP_STEP_TYPE = 'tar_zip';
    private Target $source_target;
    private Target $destination_target;
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
        if (!str_ends_with($destination, '/')) {
            $destination = $destination . '/';
        }
        $source = $this->source_target->target_url;
        if (!str_ends_with($source, '/')) {
            $source = $source . '/';
        }
        $command = "tar -cvzf {$destination}{$this->file_name} {$source}";
        $output = `$command`;
        if ($output === null) {
            throw new Exception('There was a problem running the tar command: ' . $command);
        }
    }
}
