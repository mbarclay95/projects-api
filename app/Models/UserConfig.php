<?php

namespace App\Models;

use App\Traits\HasApiModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class UserConfig
 *
 * @property integer id
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @property boolean side_menu_open
 *
 * @property integer user_id
 * @property User user
 */
class UserConfig extends Model
{
    use HasFactory, HasApiModel;

    protected static $unguarded = true;

    protected static array $apiModelAttributes = ['side_menu_open'];

    protected static array $apiModelEntities = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
