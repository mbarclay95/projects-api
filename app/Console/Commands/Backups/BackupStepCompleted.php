<?php

namespace App\Console\Commands\Backups;

use App\Models\Backups\BackupStep;
use Illuminate\Console\Command;

class BackupStepCompleted extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backups:backup-step-completed {backupStepId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $backupStepId = $this->input->getArgument('backupStepId');
        /** @var BackupStep $backupStep */
        $backupStep = BackupStep::query()->find($backupStepId);
        if (!$backupStep) {
            $this->output->error('BackupStep not found');
            return;
        }

        $backupStep->completed();
        $backupStep->backup->startNextOrComplete();
    }
}
