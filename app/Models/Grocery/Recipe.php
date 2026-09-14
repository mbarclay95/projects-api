<?php

namespace App\Models\Grocery;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Mbarclay36\LaravelCrud\ApiModel;

/**
 * Class Recipe
 *
 * @property int id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property string name
 * @property string|null description
 * @property int user_group_id
 * @property Collection|RecipeItem[] ingredients
 */
class Recipe extends ApiModel
{
    use HasFactory;

    protected static array $apiModelAttributes = ['id', 'name', 'description'];

    protected static array $apiModelArrayEntities = [
        'ingredients' => RecipeItem::class,
    ];

    public function ingredients(): HasMany
    {
        return $this->hasMany(RecipeItem::class)->orderBy('id');
    }
}
