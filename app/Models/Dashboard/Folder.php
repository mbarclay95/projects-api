<?php

namespace App\Models\Dashboard;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * Class Folder
 * @package App\Models
 *
 * @property integer id
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @property integer sort
 * @property string name
 * @property boolean show
 *
 * @property integer user_id
 * @property User user

 * @property Collection|Site[] sites
 */
class Folder extends Model
{
    use HasFactory;

    protected static $unguarded = true;

    public function sites(): HasMany
    {
        return $this->hasMany(Site::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
