<?php

namespace App\Models\Backups;

use App\Models\Users\User;
use App\Services\Backups\BackupStepTypes\DefaultBackupStepType;
use App\Services\Backups\BackupStepTypes\TarZipBackupStepType;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mbarclay36\LaravelCrud\ApiModel;

/**
 * Class BackupStep
 *
 * @property integer id
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @property string name
 * @property integer sort
 * @property array config
 * @property string backup_step_type
 *
 * @property integer user_id
 * @property User user
 *
 * @property integer backup_id
 * @property Backup backup
 */
class BackupStep extends ApiModel
{
    use HasFactory;

    protected static array $apiModelAttributes = ['id', 'name', 'sort', 'backup_step_type', 'config'];

    protected static array $apiModelEntities = [];

    protected static array $apiModelArrayEntities = [];

    protected $casts = [
        'config' => 'array'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function backup(): BelongsTo
    {
        return $this->belongsTo(Backup::class);
    }

//    public function run(): void
//    {
//        $this->started_at = Carbon::now();
//        $this->save();
//
//        try {
//            $typeService = DefaultBackupStepType::getBackupStepTypeClass($this);
//            $typeService->runStep();
//            $this->completed_at = Carbon::now();
//            $this->save();
//            $this->backup->startNextOrComplete();
//        } catch (Exception $exception) {
//            $this->errored_at = Carbon::now();
//            $this->error_message = $exception->getMessage();
//            $this->save();
//            $this->backup->errored_at = $this->errored_at;
//            $this->backup->save();
//        }


//        $folderBackup = $this->scheduled_backup_id ? "scheduled_backup_{$this->scheduled_backup_id}" : "backup_{$this->backup_id}";
//        $folderBackupStep = $this->scheduled_backup_step_id ? "step_{$this->scheduled_backup_step_id}" : "step_{$this->id}";
//
//        $full = $this->full_backup ? 'full ' : '';
//        $duplicityCommand = "{$full}{$this->source_dir} sftp://{$this->target->host_name}/{$this->target->target_url}/$folderBackup/$folderBackupStep";
//        $base = base_path();
//        $backupCompleteCommand = "{$base}/artisan backups:backup-step-completed {$this->id}";
//        $command = "$base/scripts/run_duplicity.sh '$duplicityCommand' '$backupCompleteCommand'";
//        `$command > /dev/null 2>&1 &`;
//
//        return $this;
//    }

//    public function completed(): BackupStep
//    {
//        $this->completed_at = Carbon::now();
//        $this->save();
//
//        return $this;
//    }
}
