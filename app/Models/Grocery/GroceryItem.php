<?php

namespace App\Models\Grocery;

use App\Models\Tags\Tag;
use App\Traits\HasTags;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Collection;
use Mbarclay36\LaravelCrud\ApiModel;

/**
 * Class GroceryItem
 *
 * @property int id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property string name
 * @property string|null notes
 * @property int user_group_id
 * @property Collection|Tag[] tags
 */
class GroceryItem extends ApiModel
{
    use HasFactory, HasTags;

    protected static array $apiModelAttributes = ['id', 'name', 'notes'];

    protected static array $apiModelArrayEntities = [
        'tags' => Tag::class,
    ];
}
