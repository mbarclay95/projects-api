<?php

namespace App\Models\Grocery;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Mbarclay36\LaravelCrud\ApiModel;

/**
 * Class GroceryStoreItemCategory
 *
 * @property int id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property int user_group_id
 * @property int grocery_store_id
 * @property int grocery_item_id
 * @property int grocery_category_id
 */
class GroceryStoreItemCategory extends ApiModel
{
    use HasFactory;

    protected static array $apiModelAttributes = ['id', 'grocery_store_id', 'grocery_item_id', 'grocery_category_id'];
}
