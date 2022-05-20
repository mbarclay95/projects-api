<?php

namespace App\Models\Tasks;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * Class Family
 *
 * @property integer id
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @property string name
 *
 * @property Collection|TaskUserConfig[] userConfigs
 */
class Family extends Model
{
    use HasFactory;

    protected static $unguarded = true;

    public function userConfigs(): HasMany
    {
        return $this->hasMany(TaskUserConfig::class);
    }
}
