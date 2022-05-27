<?php

namespace App\Models\Tasks;

use App\Models\BaseApiModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
class Family extends BaseApiModel
{
    use HasFactory;

    protected static array $apiModelAttributes = ['id', 'name'];

    protected static array $apiModelEntities = [];

    protected static array $apiModelArrayEntities = [];

    public function userConfigs(): HasMany
    {
        return $this->hasMany(TaskUserConfig::class);
    }
}
