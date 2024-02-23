<?php

namespace App\Models\Backups;

use App\Models\Users\User;
use App\Traits\HasApiModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Target
 *
 * @property integer id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property Carbon deleted_at
 *
 * @property string name
 * @property string target_url
 * @property string host_name
 *
 * @property integer user_id
 * @property User user
 */
class Target extends Model
{
    use HasFactory, HasApiModel;

    protected static array $apiModelAttributes = ['id', 'name', 'target_url', 'host_name'];

    protected static array $apiModelEntities = [];

    protected static array $apiModelArrayEntities = [];

    protected static $unguarded = true;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
