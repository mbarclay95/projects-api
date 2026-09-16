<?php

namespace App\Models\Grocery;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mbarclay36\LaravelCrud\ApiModel;

/**
 * Class RecipeItem
 *
 * @property int id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property int recipe_id
 * @property int grocery_item_id
 * @property float|null quantity
 * @property Recipe recipe
 * @property GroceryItem groceryItem
 */
class RecipeItem extends ApiModel
{
    use HasFactory;

    protected static array $apiModelAttributes = ['id', 'grocery_item_id', 'quantity'];

    protected $casts = [
        'quantity' => 'float',
    ];

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    public function groceryItem(): BelongsTo
    {
        return $this->belongsTo(GroceryItem::class);
    }
}
