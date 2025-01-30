<?php

namespace App\Services\Backups;

use App\Models\Backups\Backup;
use App\Models\Backups\BackupJob;
use App\Models\Backups\BackupStep;
use App\Models\Backups\BackupStepJob;
use App\Repositories\Backups\BackupJobsRepository;
use App\Services\Backups\BackupStepTypes\DefaultBackupStepType;
use Carbon\Carbon;
use Exception;

class RunBackupService
{
    private BackupJob $backupJob;

    public function __construct(Backup $backup, ?int $scheduleId = null)
    {
        $this->backupJob = $this->initBackupJob($backup, $scheduleId);
    }

    public function run(): BackupJob
    {
        $this->backupJob->started_at = Carbon::now();
        $this->backupJob->save();

        while ($this->backupJob->completed_at === null && $this->backupJob->errored_at === null) {
            $nextStep = $this->getNextStep();
            if ($nextStep === null) {
                $this->backupJob->completed_at = Carbon::now();
                $this->backupJob->save();
            } else {
                $this->runStep($nextStep);
            }
        }

        return $this->backupJob;
    }

    private function getNextStep(): BackupStepJob|null
    {
        /** @var BackupStepJob $nextStep */
        $nextStep = $this->backupJob->backupStepJobs()
                                    ->select('backup_step_jobs.*')
                                    ->whereNull('backup_step_jobs.started_at')
                                    ->join('backup_steps', 'backup_step_jobs.backup_step_id', '=', 'backup_steps.id')
                                    ->orderBy('backup_steps.sort')
                                    ->first();

        return $nextStep ?? null;
    }

    private function runStep(BackupStepJob $backupStepJob): void
    {
        $backupStepJob->started_at = Carbon::now();
        $backupStepJob->save();

        try {
            $typeService = DefaultBackupStepType::getBackupStepTypeClass($backupStepJob->backupStep);
            $typeService->runStep();
            $backupStepJob->completed_at = Carbon::now();
            $backupStepJob->save();
        } catch (Exception $exception) {
            $backupStepJob->errored_at = Carbon::now();
            $backupStepJob->error_message = $exception->getMessage();
            $backupStepJob->save();
            $this->backupJob->errored_at = $backupStepJob->errored_at;
            $this->backupJob->save();
        }
    }

    private function initBackupJob(Backup $backup, ?int $scheduleId): BackupJob
    {
        $backup->load('backupSteps');
        /** @var BackupJob $backupJob */
        $backupJob = BackupJobsRepository::createEntityStatic([
            'backup' => $backup,
            'scheduleId' => $scheduleId
        ], $backup->user);
        $backupJob->load('backupStepJobs.backupStep');

        return $backupJob;
    }
}
