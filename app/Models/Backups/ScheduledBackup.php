<?php

namespace App\Models\Backups;

use App\Models\User;
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
 * @property boolean monday
 * @property boolean tuesday
 * @property boolean wednesday
 * @property boolean thursday
 * @property boolean friday
 * @property boolean saturday
 * @property boolean sunday
 * @property integer start_time
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
    use HasFactory;

    protected static $unguarded = true;

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
}
