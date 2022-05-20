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
 * Class Backup
 *
 * @property integer id
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @property string name
 * @property Carbon started_at
 * @property Carbon completed_at
 * @property Carbon errored_at
 *
 * @property integer user_id
 * @property User user
 *
 * @property integer scheduled_backup_id
 * @property ScheduledBackup scheduledBackup
 *
 * @property Collection|BackupStep[] backupSteps
 */
class Backup extends Model
{
    use HasFactory, HasApiModel;

    protected static array $apiModelAttributes = ['id', 'name', 'started_at', 'completed_at', 'errored_at',
        'scheduled_backup_id'];

    protected static array $apiModelEntities = [];

    protected static array $apiModelArrayEntities = [
        'backupSteps' => BackupStep::class,
    ];

    protected static $unguarded = true;

    protected $dates = [
        'started_at',
        'completed_at',
        'errored_at',
    ];

    public static function create(string $name, int $userId, ?int $scheduled_backup_id = null): Backup
    {
        $backup = new Backup([
            'name' => $name,
        ]);
        $backup->user()->associate($userId);
        $backup->scheduledBackup()->associate($scheduled_backup_id);
        $backup->save();

        return $backup;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scheduledBackup(): BelongsTo
    {
        return $this->belongsTo(ScheduledBackup::class);
    }

    public function startBackup(): Backup
    {
        $this->started_at = Carbon::now();
        $this->save();

        /** @var BackupStep $firstStep */
        $firstStep = $this->backupSteps->sortBy('sort')->first();
        $firstStep->run();

        return $this;
    }

    public function startNextOrComplete(): Backup
    {
        /** @var BackupStep $nextStep */
        $nextStep = $this->backupSteps()
                         ->whereNull('completed_at')
                         ->whereNull('errored_at')
                         ->orderBy('sort')
                         ->first();

        if ($nextStep) {
            $nextStep->run();
        } else {
            $this->completed_at = Carbon::now();
            $this->save();
        }

        return $this;
    }

    public function backupSteps(): HasMany
    {
        return $this->hasMany(BackupStep::class);
    }
}
