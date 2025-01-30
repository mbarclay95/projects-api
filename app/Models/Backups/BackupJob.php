<?php

namespace App\Models\Backups;

use App\Models\Users\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Mbarclay36\LaravelCrud\ApiModel;

/**
 * Class Backup
 *
 * @property integer id
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @property Carbon started_at
 * @property Carbon completed_at
 * @property Carbon errored_at
 *
 * @property integer user_id
 * @property User user
 *
 * @property integer backup_id
 * @property Backup backup
 *
 * @property integer schedule_id
 * @property Schedule schedule
 *
 * @property Collection|BackupStepJob[] backupStepJobs
 */
class BackupJob extends ApiModel
{
    use HasFactory;

    protected static array $apiModelAttributes = ['id', 'created_at', 'started_at', 'completed_at', 'errored_at',
        'schedule_id'];

    protected static array $apiModelEntities = [];

    protected static array $apiModelArrayEntities = [
        'backupStepJobs' => BackupStepJob::class
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'errored_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function backup(): BelongsTo
    {
        return $this->belongsTo(Backup::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function backupStepJobs(): HasMany
    {
        return $this->hasMany(BackupStepJob::class);
    }
}
