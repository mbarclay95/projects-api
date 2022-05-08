<?php

namespace App\Console\Commands\Backups;

use App\Models\Backups\Backup;
use Illuminate\Console\Command;

class RunBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backups:run {backupId}';

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
        $backupId = $this->input->getArgument('backupId');
        /** @var Backup $backup */
        $backup = Backup::query()->find($backupId);
        if (!$backup) {
            $this->output->error('Backup not found');
            return;
        }

        $backup->startBackup();
    }
}
