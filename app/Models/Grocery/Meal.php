<?php

namespace App\Models\Grocery;

use App\Enums\MealSlot;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Mbarclay36\LaravelCrud\ApiModel;

/**
 * Class Meal
 *
 * @property int id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property int user_group_id
 * @property string date
 * @property MealSlot slot
 * @property int position
 * @property string name
 * @property int|null recipe_id
 */
class Meal extends ApiModel
{
    use HasFactory;

    protected static array $apiModelAttributes = ['id', 'date', 'slot', 'position', 'name', 'recipe_id'];

    protected $fillable = ['user_group_id', 'date', 'slot', 'position', 'name', 'recipe_id'];

    protected $casts = ['slot' => MealSlot::class];
}
