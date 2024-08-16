<?php

namespace App\Services\Backups\BackupStepTypes;

interface BackupStepTypeInterface
{
    function runStep(): void;
    function validateAndSetConfig(array $config): bool;
}
