<?php

namespace App\Models\Backups;

use App\Models\Users\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Mbarclay36\LaravelCrud\ApiModel;

/**
 * Class Schedule
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
 * @property Collection|Backup[] backups
 */
class Schedule extends ApiModel
{
    use HasFactory, SoftDeletes;

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

    public function backups(): BelongsToMany
    {
        return $this->belongsToMany(Backup::class);
    }

}
