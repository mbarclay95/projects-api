<?php

namespace App\Models\Backups;

use App\Models\Users\User;
use App\Traits\HasApiModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * Class ScheduledBackup
 *
 * @property integer id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property Carbon deleted_at
 *
 * @property string name
 * @property integer start_time
 * @property array schedule
 * @property boolean enabled
 * @property integer full_every_n_days
 *
 * @property integer user_id
 * @property User user
 *
 * @property Collection|ScheduledBackupStep[] scheduledBackupSteps
 * @property Collection|Backup[] backups
 * @property Collection|BackupStep[] backupSteps
 */
class ScheduledBackup extends Model
{
    use HasFactory, HasApiModel;

    protected static array $apiModelAttributes = ['id', 'name', 'start_time', 'schedule', 'full_every_n_days',
        'enabled'];

    protected static array $apiModelEntities = [];

    protected static array $apiModelArrayEntities = [
        'scheduledBackupSteps' => ScheduledBackupStep::class
    ];

    protected static $unguarded = true;

    protected $casts = [
        'schedule' => 'jsonb'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scheduledBackupSteps(): HasMany
    {
        return $this->hasMany(ScheduledBackupStep::class);
    }

    public function backups(): HasMany
    {
        return $this->hasMany(Backup::class);
    }

    public function backupSteps(): HasMany
    {
        return $this->hasMany(BackupStep::class);
    }

    public static function createFromRequest($request, int $userId): ScheduledBackup
    {
        $scheduledBackup = new ScheduledBackup([
            'name' => $request['name'],
            'enabled' => $request['enabled'],
            'start_time' => $request['startTime'],
            'full_every_n_days' => $request['fullEveryNDays'],
            'schedule' => $request['schedule'],
        ]);
        $scheduledBackup->user()->associate($userId);
        $scheduledBackup->save();

        return $scheduledBackup;
    }

}
