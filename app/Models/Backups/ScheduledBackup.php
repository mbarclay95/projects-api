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
 * Class ScheduledBackup
 *
 * @property integer id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property Carbon deleted_at
 *
 * @property string name
 * @property array schedule
 * @property boolean enabled
 *
 * @property integer user_id
 * @property User user
 *
 * @property integer backup_id
 * @property Backup backup
 *
 * @property Collection|Backup[] backups
 */
class ScheduledBackup extends ApiModel
{
    use HasFactory;

    protected static array $apiModelAttributes = ['id', 'name', 'schedule', 'enabled'];

    protected static array $apiModelEntities = [];

    protected static array $apiModelArrayEntities = [];

    protected $casts = [
        'schedule' => 'jsonb'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function backup(): BelongsTo
    {
        return $this->belongsTo(Backup::class);
    }

    public function backups(): HasMany
    {
        return $this->hasMany(Backup::class);
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
