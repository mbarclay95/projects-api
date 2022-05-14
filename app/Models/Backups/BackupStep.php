<?php

namespace App\Models\Backups;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class BackupStep
 *
 * @property integer id
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @property string name
 * @property boolean full_backup
 * @property string source_dir
 * @property Carbon started_at
 * @property Carbon completed_at
 * @property Carbon errored_at
 * @property integer sort
 *
 * @property integer user_id
 * @property User user
 *
 * @property integer target_id
 * @property Target target
 *
 * @property integer backup_id
 * @property Backup backup
 *
 * @property integer scheduled_backup_id
 * @property ScheduledBackup scheduledBackup
 *
 * @property integer scheduled_backup_step_id
 * @property ScheduledBackupStep scheduledBackupStep
 */
class BackupStep extends Model
{
    use HasFactory;

    protected static $unguarded = true;

    protected $dates = [
        'started_at',
        'completed_at',
        'errored_at',
    ];

    public static function createFromRequest(array $request, int $userId, int $backupId): BackupStep
    {
        $backupStep = new BackupStep([
            'name' => $request['name'],
            'sort' => $request['sort'],
            'source_dir' => $request['sourceDir'],
            'full_backup' => $request['fullBackup'],
        ]);
        $backupStep->user()->associate($userId);
        $backupStep->target()->associate($request['target']['id']);
        $backupStep->backup()->associate($backupId);
        $backupStep->save();

        return $backupStep;
    }

    public static function createBackupStep(string $name, int $userId, int $targetId, int $sort, string $sourceDir, bool $fullBackup): BackupStep
    {
        $backupStep = new BackupStep([
            'name' => $name,
            'sort' => $sort,
            'source_dir' => $sourceDir,
            'full_backup' => $fullBackup,
        ]);
        $backupStep->user()->associate($userId);
        $backupStep->target()->associate($targetId);
        $backupStep->save();

        return $backupStep;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(Target::class);
    }

    public static function createFromScheduled(ScheduledBackupStep $scheduledBackupStep): BackupStep
    {
        $fullBackup = true;
        $lastFull = BackupStep::getLastFullBackupStep($scheduledBackupStep->id);
        if ($lastFull) {
            $fullBackup = $lastFull->completed_at->addDays($scheduledBackupStep->full_every_n_days) > Carbon::today();
        }

        $backupStep = new BackupStep([
            'name' => $scheduledBackupStep->name,
            'source_dir' => $scheduledBackupStep->source_dir,
            'sort' => $scheduledBackupStep->sort,
            'full_backup' => $fullBackup
        ]);
        $backupStep->user()->associate($scheduledBackupStep->user_id);
        $backupStep->target()->associate($scheduledBackupStep->target_id);
        $backupStep->scheduledBackupStep()->associate($scheduledBackupStep->id);
        $backupStep->scheduledBackup()->associate($scheduledBackupStep->scheduled_backup_id);
        $backupStep->save();

        return $backupStep;
    }

    public function scheduledBackupStep(): BelongsTo
    {
        return $this->belongsTo(ScheduledBackupStep::class);
    }

    public function scheduledBackup(): BelongsTo
    {
        return $this->belongsTo(ScheduledBackup::class);
    }

    public static function getLastFullBackupStep(int $scheduledBackupId): BackupStep|null
    {
        /** @var BackupStep|null $lastFull */
        $lastFull = BackupStep::query()
                              ->where('scheduled_backup_step_id', '=', $scheduledBackupId)
                              ->whereNull('errored_at')
                              ->orderBy('completed_at', 'desc')
                              ->first();

        return $lastFull;
    }

    public function backup(): BelongsTo
    {
        return $this->belongsTo(Backup::class);
    }

    public function run(): BackupStep
    {
        $this->started_at = Carbon::now();
        $this->save();

        $full = $this->full_backup ? 'full ' : '';
        $duplicityCommand = "{$full}{$this->source_dir} sftp://{$this->target->host_name}/{$this->target->target_url}/2022-05-13.5";
        $base = base_path();
        $backupCompleteCommand = "{$base}/artisan backups:backup-step-completed {$this->id}";
        $command = "$base/scripts/run_duplicity.sh '$duplicityCommand' '$backupCompleteCommand'";
        `$command > /dev/null 2>&1 &`;

        return $this;
    }

    public function completed(): BackupStep
    {
        $this->completed_at = Carbon::now();
        $this->save();

        return $this;
    }
}
