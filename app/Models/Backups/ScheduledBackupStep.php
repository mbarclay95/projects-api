<?php

namespace App\Models\Backups;

use App\Models\HasApiModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * Class ScheduledBackupStep
 *
 * @property integer id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property Carbon deleted_at
 *
 * @property string name
 * @property string source_dir
 * @property integer sort
 *
 * @property integer user_id
 * @property User user
 *
 * @property integer target_id
 * @property Target target
 *
 * @property integer scheduled_backup_id
 * @property ScheduledBackup scheduledBackup
 *
 * @property Collection|BackupStep[] backupSteps
 */
class ScheduledBackupStep extends Model
{
    use HasFactory, HasApiModel;

    protected static array $apiModelAttributes = ['id', 'name', 'source_dir', 'sort'];

    protected static array $apiModelEntities = [
        'target' => Target::class
    ];

    protected static array $apiModelArrayEntities = [];

    protected static $unguarded = true;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(Target::class);
    }

    public function scheduledBackup(): BelongsTo
    {
        return $this->belongsTo(ScheduledBackup::class);
    }

    public function backupSteps(): HasMany
    {
        return $this->hasMany(BackupStep::class);
    }

    public static function createFromRequest(array $request, int $userId, int $scheduleBackupId): ScheduledBackupStep
    {
        $scheduleBackupStep = new ScheduledBackupStep([
            'name' => $request['name'],
            'sort' => $request['sort'],
            'source_dir' => $request['sourceDir'],
            'full_backup' => $request['fullBackup'],
        ]);
        $scheduleBackupStep->user()->associate($userId);
        $scheduleBackupStep->target()->associate($request['target']['id']);
        $scheduleBackupStep->scheduledBackup()->associate($scheduleBackupId);
        $scheduleBackupStep->save();

        return $scheduleBackupStep;
    }
}
