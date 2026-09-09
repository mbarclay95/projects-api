<?php

namespace App\Models\Grocery;

use App\Models\ApiModels\UserGroupMemberApiModel;
use App\Models\Users\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mbarclay36\LaravelCrud\ApiModel;

/**
 * Class GroceryListItem
 *
 * @property int id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property int grocery_item_id
 * @property GroceryItem groceryItem
 * @property int user_group_id
 * @property int added_by_user_id
 * @property User addedBy
 * @property float|null quantity
 * @property Carbon|null bought_at
 * @property bool bought
 */
class GroceryListItem extends ApiModel
{
    use HasFactory;

    protected static array $apiModelAttributes = ['id', 'grocery_item_id', 'quantity', 'bought'];

    protected static array $apiModelEntities = [
        'groceryItem' => GroceryItem::class,
        'addedBy' => UserGroupMemberApiModel::class,
    ];

    protected $casts = [
        'quantity' => 'float',
        'bought_at' => 'datetime',
    ];

    public function getBoughtAttribute(): bool
    {
        return $this->bought_at !== null;
    }

    public function groceryItem(): BelongsTo
    {
        return $this->belongsTo(GroceryItem::class);
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by_user_id');
    }
}
