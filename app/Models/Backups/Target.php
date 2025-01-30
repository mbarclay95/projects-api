<?php

namespace App\Models\Backups;

use App\Models\Users\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mbarclay36\LaravelCrud\ApiModel;

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
class Target extends ApiModel
{
    use HasFactory, SoftDeletes;

    protected static array $apiModelAttributes = ['id', 'name', 'target_url', 'host_name'];

    protected static array $apiModelEntities = [];

    protected static array $apiModelArrayEntities = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
