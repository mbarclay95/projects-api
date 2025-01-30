<?php

namespace App\Models\Backups;

use App\Models\Users\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Mbarclay36\LaravelCrud\ApiModel;

/**
 * Class Backup
 *
 * @property integer id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property Carbon deleted_at
 *
 * @property string name
 *
 * @property integer user_id
 * @property User user
 *
 * @property Collection|BackupStep[] backupSteps
 * @property Collection|Schedule[] schedules
 * @property Collection|BackupJob[] backupJobs
 */
class Backup extends ApiModel
{
    use HasFactory, SoftDeletes;

    protected static array $apiModelAttributes = ['id', 'name'];

    protected static array $apiModelEntities = [];

    protected static array $apiModelArrayEntities = [
        'backupSteps' => BackupStep::class,
        'backupJobs' => BackupJob::class,
        'schedules' => Schedule::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function schedules(): BelongsToMany
    {
        return $this->belongsToMany(Schedule::class);
    }

    public function backupJobs(): HasMany
    {
        return $this->hasMany(BackupJob::class);
    }

//    public function startNextOrComplete(): Backup
//    {
//        /** @var BackupStep $nextStep */
//        $nextStep = $this->backupSteps()
//                         ->whereNull('completed_at')
//                         ->whereNull('errored_at')
//                         ->orderBy('sort')
//                         ->first();
//
//        if ($nextStep) {
//            $nextStep->run();
//        } else {
//            $this->completed_at = Carbon::now();
//            $this->save();
//        }
//
//        return $this;
//    }

    public function backupSteps(): HasMany
    {
        return $this->hasMany(BackupStep::class);
    }
}
