<?php

namespace App\Models\Tasks;

use App\Models\BaseApiModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;

/**
 * Class RecurringTask
 *
 * @property integer id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property Carbon deleted_at
 *
 * @property string name
 * @property string description
 * @property integer frequency_amount
 * @property string frequency_unit
 *
 * @property string owner_type
 * @property integer owner_id
 * @property User|Family owner
 *
 * @property Collection|Tag[] tags
 */
class RecurringTask extends BaseApiModel
{
    use HasFactory;

    protected static array $apiModelAttributes = ['id', 'name', 'description', 'frequency_amount', 'frequency_unit',
        'owner_type', 'owner_id'];

    protected static array $apiModelEntities = [];

    protected static array $apiModelArrayEntities = [];

    public function owner(): MorphTo
    {
        return $this->morphTo('owner', 'owner_type', 'owner_id');
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}
