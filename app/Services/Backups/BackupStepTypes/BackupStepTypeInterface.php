<?php

namespace App\Services\Backups\BackupStepTypes;

interface BackupStepTypeInterface
{
    public function runStep(): void;

    public function validateAndSetConfig(array $config): bool;
}
