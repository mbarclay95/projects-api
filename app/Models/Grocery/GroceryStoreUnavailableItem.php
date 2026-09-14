<?php

namespace App\Models\Grocery;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mbarclay36\LaravelCrud\ApiModel;

/**
 * Class GroceryStoreUnavailableItem
 *
 * @property int id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property int user_group_id
 * @property int grocery_store_id
 * @property int grocery_item_id
 * @property GroceryStore groceryStore
 * @property GroceryItem groceryItem
 */
class GroceryStoreUnavailableItem extends ApiModel
{
    use HasFactory;

    protected static array $apiModelAttributes = ['id', 'grocery_store_id', 'grocery_item_id'];

    public function groceryStore(): BelongsTo
    {
        return $this->belongsTo(GroceryStore::class);
    }

    public function groceryItem(): BelongsTo
    {
        return $this->belongsTo(GroceryItem::class);
    }
}
