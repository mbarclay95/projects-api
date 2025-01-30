<?php

namespace App\Models\Backups;

use App\Models\Users\User;
use Carbon\Carbon;
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
 * @property Carbon started_at
 * @property Carbon completed_at
 * @property Carbon errored_at
 * @property string error_message
 *
 * @property integer user_id
 * @property User user
 *
 * @property integer backup_step_id
 * @property BackupStep backupStep
 *
 * @property integer backup_job_id
 * @property BackupJob backupJob
 */
class BackupStepJob extends ApiModel
{
    use HasFactory;

    protected static array $apiModelAttributes = ['id', 'started_at', 'completed_at', 'errored_at', 'error_message',
        'backup_step_id'];

    protected static array $apiModelEntities = [];

    protected static array $apiModelArrayEntities = [];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'errored_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function backupStep(): BelongsTo
    {
        return $this->belongsTo(BackupStep::class);
    }

    public function backupJob(): BelongsTo
    {
        return $this->belongsTo(BackupJob::class);
    }
}
