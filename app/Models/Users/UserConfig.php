<?php

namespace App\Models\Users;

use App\Traits\HasApiModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class UserConfig
 *
 * @property int id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property bool side_menu_open
 * @property bool home_page_role
 * @property bool money_app_token
 * @property int user_id
 * @property User user
 */
class UserConfig extends Model
{
    use HasApiModel, HasFactory;

    protected static $unguarded = true;

    protected static array $apiModelAttributes = ['side_menu_open', 'home_page_role', 'money_app_token'];

    protected static array $apiModelEntities = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
